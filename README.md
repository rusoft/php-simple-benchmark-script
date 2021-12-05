# Простой скрипт проверки быстродействия PHP

Работает со всеми версиями ПХП: от 4.3 до 8.1

## Зависимости

Необходимы модули для php:

- pcre
- mbstring
- json
- dom
- simplexml
- intl

Обычно они уже установлены или "вкомпилированны" в php.

Проверить наличие:

- в консоли `php -m`
- или через вывод функции `phpinfo()`

## Запуск

### 0. Файлы

Нужно положить в один каталог файлы: `bench.php`, `common.inc`, `php5.inc`, `php7.inc`, `test.xml`.

### 1. Через консоль

Команда:
```
Usage: bench.php [-h|--help] [-d|--dont-recalc] [-D|--dumb-test-print] [-L|--list-tests] [-I|--system-info] [-S|--do-not-task-set] [-m|--memory-limit=256] [-t|--time-limit=600] [-T|--run-test=name1 ...]

	-h|--help		- print this help and exit
	-d|--dont-recalc	- do not recalculate test times / operations count even if memory of execution time limits are low
	-D|--dumb-test-print	- print dumb test time, for debug purpose
	-L|--list-tests		- output list of available tests and exit
	-I|--system-info	- output system info but do not run tests and exit
	-S|--do-not-task-set	- if run on cli - dont call taskset to pin process to one cpu core
	-m|--memory-limit <Mb>	- set memory_limit value in Mb, defaults to 256 (Mb)
	-t|--time-limit <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)
	-T|--run-test <name>	- run selected test, test names from --list-tests output, can be defined multiple times
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

В этом случае скрипт не сможет установить переданные в него значения параметров,
по крайней мере не выше лимитов.

Пересчет времени выполнения скрипта будет произведен по наименьшим результирующим значениям.

## Пример вывода скрипта

```
-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2021-12-05 17:43:01
Server              : Linux/5.4.0-90-lowlatency x86_64
Platform            : Linux
System              : Ubuntu 18.04.6 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-6600K CPU @ 3.50GHz
              cores : 4
          available : 4
                MHz : 3790.323MHz
Benchmark version   : 1.0.44-dev
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
               intl : yes; version: 60.2
            opcache : yes; enabled: 0
             xdebug : no
Set time limit      : 600 sec
Crypt hash algo     : MD5
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    3.092 sec | 646.90 kOp/s | 170.67  Ops/MHz |      2 Mb
02_string_concat               :    1.264 sec |  27.70 MOp/s |   7.31 kOps/MHz | 117.49 Mb
03_1_string_number_concat      :    1.670 sec |   2.99 MOp/s | 790.03  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.490 sec |   3.36 MOp/s | 885.54  Ops/MHz |      4 Mb
04_string_simple_functions     :    1.407 sec | 924.07 kOp/s | 243.80  Ops/MHz |      4 Mb
05_string_multibyte            :    1.111 sec | 116.99 kOp/s |  30.87  Ops/MHz |      4 Mb
06_string_manipulation         :    2.678 sec | 485.38 kOp/s | 128.06  Ops/MHz |      4 Mb
07_regex                       :    2.290 sec | 567.79 kOp/s | 149.80  Ops/MHz |      4 Mb
08_1_hashing                   :    2.065 sec | 629.41 kOp/s | 166.06  Ops/MHz |      4 Mb
08_2_crypt                     :    8.903 sec |   1.12 kOp/s |   0.30  Ops/MHz |      4 Mb
09_json_encode                 :    2.361 sec | 550.70 kOp/s | 145.29  Ops/MHz |      4 Mb
10_json_decode                 :    3.548 sec | 366.38 kOp/s |  96.66  Ops/MHz |      4 Mb
11_serialize                   :    1.692 sec | 768.23 kOp/s | 202.68  Ops/MHz |      4 Mb
12_unserialize                 :    1.674 sec | 776.75 kOp/s | 204.93  Ops/MHz |      4 Mb
13_array_fill                  :    4.062 sec |  22.16 MOp/s |   5.85 kOps/MHz |     24 Mb
14_array_range                 :    1.359 sec | 110.37 kOp/s |  29.12  Ops/MHz |     24 Mb
14_array_unset                 :    3.005 sec |  29.95 MOp/s |   7.90 kOps/MHz |     24 Mb
15_loops                       :    1.385 sec | 288.76 MOp/s |  76.18 kOps/MHz |      4 Mb
16_loop_ifelse                 :    2.029 sec |  49.29 MOp/s |  13.00 kOps/MHz |      4 Mb
17_loop_ternary                :    3.223 sec |  31.03 MOp/s |   8.19 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    1.137 sec |  43.98 MOp/s |  11.60 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    4.792 sec |  10.43 MOp/s |   2.75 kOps/MHz |      4 Mb
19_type_functions              :    1.325 sec |   3.77 MOp/s | 995.35  Ops/MHz |      4 Mb
20_type_conversion             :    1.338 sec |   3.74 MOp/s | 985.97  Ops/MHz |      4 Mb
21_0_loop_exception_none       :    0.214 sec |  46.84 MOp/s |  12.36 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.214 sec |  46.77 MOp/s |  12.34 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    3.050 sec |   3.28 MOp/s | 865.11  Ops/MHz |      4 Mb
22_loop_null_op                :    1.313 sec |  45.71 MOp/s |  12.06 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    1.232 sec |  48.69 MOp/s |  12.85 kOps/MHz |      4 Mb
26_1_class_public_properties   :    0.131 sec |  76.23 MOp/s |  20.11 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.423 sec |  23.64 MOp/s |   6.24 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    1.294 sec |   7.73 MOp/s |   2.04 kOps/MHz |      4 Mb
27_simplexml                   :    4.139 sec |  12.08 kOp/s |   3.19  Ops/MHz |      4 Mb
28_domxml                      :    4.360 sec |  11.47 kOp/s |   3.03  Ops/MHz |      4 Mb
29_datetime                    :    0.832 sec | 600.61 kOp/s | 158.46  Ops/MHz |      4 Mb
30_intl_number_format          :    1.089 sec |  18.36 kOp/s |   4.84  Ops/MHz |      4 Mb
31_intl_message_format         :    2.122 sec |  94.27 kOp/s |  24.87  Ops/MHz |      4 Mb
32_intl_calendar               :    0.847 sec | 354.19 kOp/s |  93.45  Ops/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :   80.157 sec |  14.08 MOp/s |   3.72 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :   114.17 Mb
```
