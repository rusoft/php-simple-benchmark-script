# Простой скрипт проверки быстродействия PHP

Работает со всеми версиями ПХП: от 4.3 до 8.0

## Зависимости

Необходимы модули для php:

- pcre
- mbstring
- json

Обычно они уже установлены или "вкомпилированны" в php.

Проверить наличие:

- в консоли `php -m`
- или через вывод функции `phpinfo()`

## Запуск

### 0. Файлы

Нужно положить в один каталог файлы: `bench.php`, `common.inc`, `php5.inc`, `php7.inc`

### 1. Через консоль

Команда:
```
php bench.php [-h|--help] [-d|--dont-recalc] [-D|--dumb-test-print] [-L|--list-tests] [-I|--system-info] [-m|--memory-limit=256] [-t|--time-limit=600] [-T|--run-test=name1 ...]

	-h|--help		- вывод помощи и выход
	-d|--dont-recalc	- не пересчитывать время выполнения тестов, если ограничения слишком низкие
	-D|--dumb-test-print	- вывод времени выполнения тупого теста, для отладки
	-L|--list-tests		- вывод списка доступных тестов и выход
	-I|--system-info	- вывод информации о системе без запуска тестов и выход
	-m|--memory-limit <Mb>	- установка значения параметра `memory_limit` в Мб, по-умолчанию равно 256 (Мб)
	-t|--time-limit <sec>	- установка значения параметра `max_execution_time` в секундах, по-умолчанию равно 600 (сек)
	-T|--run-test <name>	- запустить только указанные тесты, названия тестов из вывода параметра --list-tests, можно указать несколько раз
```
Например: `php bench.php -m=64 -t=30`

Второй вариант передачи значений для параметров - переменные окружения:
```
env PHP_MEMORY_LIMIT=64 PHP_TIME_LIMIT=30 php bench.php
```

Доступные переменные:

- PHP_MEMORY_LIMIT=<Мб>
- PHP_TIME_LIMIT=<Секунды>
- DONT_RECALCULATE_LIMITS=0/1
- LIST_TESTS=0/1
- SYSTEM_INFO=0/1
- RUN_TESTS=test1,test2,...

### 2. Через веб-сервера (apache + php)

Просто положите в любую доступную для выполнения php директорию сайта, например в корень.

Потом скрипт можно будет вызывать с параметрами, как из консоли:
`curl http://www.example.com/bench.php?memory_limit=64&time_limit=30`
или через браузер.

Доступные параметры:

- memory_limit=Мб
- time_limit=Секунды
- dont_recalculate_limits=0/1
- list_tests=0/1
- system_info=0/1
- run_tests=test1,test2,...

### Учет параметров хостинга

На многих хостингах параметры `memory_limit` и `max_execution_time` могут быть жестко зафиксированы.

В этом случа скрипт не сможет установить переданные в него значения параметров,
по крайней мере не выше лимитов.

Пересчет времени выполнения скрипта будет произведен по наименьшим результирующим значениям.

## Пример вывода скрипта

```

-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2021-11-21 21:17:41
Server              : Linux/5.4.0-90-lowlatency x86_64
Platform            : Linux
System              : Ubuntu 18.04.6 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-6600K CPU @ 3.50GHz
              cores : 4
          available : 4
                MHz : 3790.071MHz
Benchmark version   : 1.0.42
PHP version         : 8.0.12-SergeyD/2
PHP time limit      : 0 sec
PHP memory limit    : 128M
Memory              : 256 Mb available
     loaded modules :
               json : yes
           mbstring : yes
               pcre : yes; version: 10.39 2021-10-29
          simplexml : yes; libxml version: 2.9.4
                dom : yes
            opcache : yes; enabled: 0
             xdebug : no
Set time limit      : 600 sec
Crypt hash algo     : MD5
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    3.084 sec | 648.56 kOp/s | 171.12  Ops/MHz |      2 Mb
02_string_concat               :    1.234 sec |  28.36 MOp/s |   7.48 kOps/MHz | 117.49 Mb
03_1_string_number_concat      :    1.638 sec |   3.05 MOp/s | 805.22  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.448 sec |   3.45 MOp/s | 910.92  Ops/MHz |      4 Mb
04_string_simple_functions     :    1.371 sec | 948.51 kOp/s | 250.26  Ops/MHz |      4 Mb
05_string_multibyte            :    1.079 sec | 120.48 kOp/s |  31.79  Ops/MHz |      4 Mb
06_string_manipulation         :    2.579 sec | 504.07 kOp/s | 133.00  Ops/MHz |      4 Mb
07_regex                       :    2.193 sec | 592.89 kOp/s | 156.43  Ops/MHz |      4 Mb
08_1_hashing                   :    1.997 sec | 650.82 kOp/s | 171.72  Ops/MHz |      4 Mb
08_2_crypt                     :    8.864 sec |   1.13 kOp/s |   0.30  Ops/MHz |      4 Mb
09_json_encode                 :    2.319 sec | 560.50 kOp/s | 147.89  Ops/MHz |      4 Mb
10_json_decode                 :    3.550 sec | 366.20 kOp/s |  96.62  Ops/MHz |      4 Mb
11_serialize                   :    1.702 sec | 763.61 kOp/s | 201.48  Ops/MHz |      4 Mb
12_unserialize                 :    1.682 sec | 773.01 kOp/s | 203.96  Ops/MHz |      4 Mb
13_array_fill                  :    3.772 sec |  23.86 MOp/s |   6.29 kOps/MHz |     24 Mb
14_array_range                 :    1.176 sec | 127.53 kOp/s |  33.65  Ops/MHz |     24 Mb
14_array_unset                 :    2.610 sec |  34.48 MOp/s |   9.10 kOps/MHz |     24 Mb
15_loops                       :    1.289 sec | 310.30 MOp/s |  81.87 kOps/MHz |      4 Mb
16_loop_ifelse                 :    1.925 sec |  51.95 MOp/s |  13.71 kOps/MHz |      4 Mb
17_loop_ternary                :    3.053 sec |  32.76 MOp/s |   8.64 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    1.125 sec |  44.43 MOp/s |  11.72 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    4.633 sec |  10.79 MOp/s |   2.85 kOps/MHz |      4 Mb
19_type_functions              :    1.251 sec |   4.00 MOp/s |   1.05 kOps/MHz |      4 Mb
20_type_conversion             :    1.258 sec |   3.98 MOp/s |   1.05 kOps/MHz |      4 Mb
21_0_loop_exception_none       :    0.211 sec |  47.39 MOp/s |  12.50 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.209 sec |  47.91 MOp/s |  12.64 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    3.093 sec |   3.23 MOp/s | 853.00  Ops/MHz |      4 Mb
22_loop_null_op                :    1.273 sec |  47.13 MOp/s |  12.44 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    1.223 sec |  49.08 MOp/s |  12.95 kOps/MHz |      4 Mb
26_1_class_public_properties   :    0.132 sec |  75.52 MOp/s |  19.93 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.427 sec |  23.43 MOp/s |   6.18 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    1.241 sec |   8.06 MOp/s |   2.13 kOps/MHz |      4 Mb
27_simplexml                   :    4.028 sec |  12.41 kOp/s |   3.28  Ops/MHz |      4 Mb
28_domxml                      :    4.084 sec |  12.24 kOp/s |   3.23  Ops/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :   72.754 sec |  15.50 MOp/s |   4.09 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :   114.15 Mb
```
