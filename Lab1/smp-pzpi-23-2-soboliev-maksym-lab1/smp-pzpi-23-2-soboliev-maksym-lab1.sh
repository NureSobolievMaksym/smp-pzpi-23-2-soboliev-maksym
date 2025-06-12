#!/bin/bash

# ==============================================================================
# Скрипт для перетворення розкладу з CIST NURE у формат Google Calendar
# Автор: Maksym Soboliev
# Версія: 1.1.0 (адаптовано до реального формату CSV)
# ==============================================================================

# --- Змінні та константи ---
readonly SCRIPT_NAME=$(basename "$0")
readonly VERSION="1.1.0"
readonly AUTHOR="Maksym Soboliev"
QUIET_MODE=0

# --- Функції ---

# Виводить довідкову інформацію
function print_help() {
  cat << EOF
Usage: $SCRIPT_NAME [--help | --version] | [[-q|--quiet] [group_name] input_cist_file.csv]

Перетворює CSV-файл розкладу з cist.nure.ua у формат для Google Calendar.
Вхідний файл має бути в кодуванні Windows-1251.

Options:
  --help              Вивести цю довідку та вийти.
  --version           Вивести інформацію про версію та вийти.
  -q, --quiet         Тихий режим. Не виводити результат у стандартний потік.

Arguments:
  group_name          Назва академічної групи для фільтрації (напр. 'ПЗПІ-23-1').
  input_cist_file.csv Шлях до вхідного CSV-файлу з розкладом.

Якщо аргументи не вказані, скрипт перейде в інтерактивний режим вибору.
EOF
}

# Виводить інформацію про версію
function print_version() {
  echo "$SCRIPT_NAME version $VERSION"
  echo "Author: $AUTHOR"
}

# Виводить повідомлення про помилку та завершує роботу
function die() {
  echo "ПОМИЛКА: $1" >&2
  exit "${2:-1}"
}

# --- Головний блок обробки ---

# Обробка ключів --help та --version
if [[ "$1" == "--help" ]]; then
  print_help
  exit 0
elif [[ "$1" == "--version" ]]; then
  print_version
  exit 0
fi

# Обробка тихого режиму
if [[ "$1" == "-q" || "$1" == "--quiet" ]]; then
  QUIET_MODE=1
  shift # Зсуваємо аргументи, щоб далі обробляти назву групи та файл
fi

# Визначення вхідних параметрів
ACADEM_GROUP=$1
INPUT_FILE=$2

# --- Інтерактивний режим, якщо параметри не задано ---
if [[ -z "$ACADEM_GROUP" || -z "$INPUT_FILE" ]]; then
  echo "Аргументи не задано. Перехід в інтерактивний режим."

  # 1. Вибір файлу
  PS3="Виберіть файл розкладу: "
  # Знаходимо файли, сортуємо за роком, місяцем, днем
  mapfile -t files < <(ls -1 TimeTable_??_??_20??.csv 2>/dev/null | sort -t_ -k4n -k3n -k2n)
  if [ ${#files[@]} -eq 0 ]; then
    die "У поточній директорії не знайдено файлів розкладу (TimeTable_*.csv)."
  fi

  select INPUT_FILE in "${files[@]}"; do
    if [[ -n "$INPUT_FILE" ]]; then
      echo "Вибрано файл: $INPUT_FILE"
      break
    else
      echo "Неправильний вибір. Спробуйте ще раз."
    fi
  done

  # 2. Вибір групи
  while true; do
    # Генеруємо список унікальних груп з файлу
    # grep -o знаходить усі збіги з шаблоном групи
    mapfile -t groups < <(iconv -f WINDOWS-1251 -t UTF-8 "$INPUT_FILE" 2>/dev/null | sed 's/"//g' | cut -d, -f1 | grep -oE '[А-ЯІЇЄ]{4}-[0-9]{2}-[0-9]{1,2}' | sort | uniq)
    
    if [ ${#groups[@]} -eq 0 ]; then
      die "У файлі '$INPUT_FILE' не знайдено жодної групи, що відповідає шаблону."
    elif [ ${#groups[@]} -eq 1 ]; then
      ACADEM_GROUP=${groups[0]}
      echo "У файлі знайдено лише одну групу: $ACADEM_GROUP. Буде оброблено її."
      break
    else
      PS3="Виберіть академічну групу: "
      select group_choice in "${groups[@]}"; do
        if [[ -n "$group_choice" ]]; then
          ACADEM_GROUP=$group_choice
          echo "Вибрано групу: $ACADEM_GROUP"
          break 2 # Виходимо з обох циклів (select та while)
        else
          echo "Неправильний вибір. Спробуйте ще раз."
        fi
      done
    fi
  done
fi

# Перевірка доступності вхідного файлу
if [ ! -f "$INPUT_FILE" ]; then
  die "Файл '$INPUT_FILE' не знайдено." 2
fi
if [ ! -r "$INPUT_FILE" ]; then
  die "Файл '$INPUT_FILE' недоступний для читання." 3
fi

# Формування імені вихідного файлу
OUTPUT_FILE="Google_$(basename "$INPUT_FILE")"

# --- Основна логіка перетворення файлу ---

# awk-скрипт для обробки даних
# -F, - роздільник полів
# -v group="$ACADEM_GROUP" - передаємо назву групи всередину awk
AWK_SCRIPT='
BEGIN {
  OFS=","; # Output Field Separator
  FS=",";  # Input Field Separator
  split("", lesson_counts); # Масив для лічильників занять
  found_count = 0;
}
# Пропускаємо заголовок
NR == 1 { next }

# Фільтруємо рядки, де в першому полі ($1) є назва нашої групи
$1 ~ group {
  # Поле 1: Тема (Subject)
  subject_raw = $1;
  # Видаляємо назву групи та дефіс на початку
  sub(group " - ", "", subject_raw);
  # Інкрементуємо лічильник для даної теми заняття
  lesson_counts[subject_raw]++;
  final_subject = "\"" subject_raw "; №" lesson_counts[subject_raw] "\"";

  # Поля 2 і 3: Дата та час початку
  start_date_raw = $2;
  start_time_raw = $3;
  split(start_date_raw, d_start, ".");
  start_date = d_start[2] "/" d_start[1] "/" d_start[3];
  
  split(start_time_raw, t_start, ":");
  h_start = t_start[1] + 0;
  ampm_start = (h_start >= 12) ? "PM" : "AM";
  if (h_start > 12) h_start -= 12;
  if (h_start == 0) h_start = 12;
  start_time = sprintf("%d:%s %s", h_start, t_start[2], ampm_start);

  # Поля 4 і 5: Дата та час кінця
  end_date_raw = $4;
  end_time_raw = $5;
  split(end_date_raw, d_end, ".");
  end_date = d_end[2] "/" d_end[1] "/" d_end[3];

  split(end_time_raw, t_end, ":");
  h_end = t_end[1] + 0;
  ampm_end = (h_end >= 12) ? "PM" : "AM";
  if (h_end > 12) h_end -= 12;
  if (h_end == 0) h_end = 12;
  end_time = sprintf("%d:%s %s", h_end, t_end[2], ampm_end);

  # Поле 12: Опис (Description)
  description = "\"" $12 "\"";

  # Виводимо відформатований рядок
  print final_subject, start_date, start_time, end_date, end_time, description;
  found_count++;
}

END {
  if (found_count == 0) {
    print "ERROR: Групу \"" group "\" не знайдено у файлі." > "/dev/stderr";
    exit 4;
  }
}
'

# Заголовок для Google Calendar CSV
HEADER="Subject,Start Date,Start Time,End Date,End time,Description"

# Виконуємо конвеєр команд, зберігаючи результат
# 1. Конвертуємо кодування, 2. Видаляємо лапки, 3. Обробляємо через awk
PROCESSING_RESULT=$(iconv -f WINDOWS-1251 -t UTF-8 "$INPUT_FILE" | sed 's/"//g' | awk -v group="$ACADEM_GROUP" "$AWK_SCRIPT")
AWK_EXIT_CODE=$?

# Перевіряємо, чи awk завершився з помилкою (наприклад, не знайшов групу)
if [ $AWK_EXIT_CODE -ne 0 ]; then
    # Повідомлення про помилку вже виведено awk-скриптом
    exit $AWK_EXIT_CODE
fi

# Створюємо фінальний файл
# tee - записує у файл і (за умовою) виводить на екран
(echo "$HEADER"; echo "$PROCESSING_RESULT") | if [ $QUIET_MODE -eq 0 ]; then
    tee "$OUTPUT_FILE"
else
    cat > "$OUTPUT_FILE"
fi

echo -e "\nПеретворення успішно завершено."
echo "Результат збережено у файлі: $OUTPUT_FILE"
exit 0