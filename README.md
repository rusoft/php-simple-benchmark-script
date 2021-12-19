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
Usage: bench.php [-h|--help] [-x|--debug] [-d|--dont-recalc] [-D|--dumb-test-print] [-L|--list-tests] [-I|--system-info] [-S|--do-not-task-set] [-m|--memory-limit=130] [-t|--time-limit=600] [-T|--run-test=name1 ...]

	-h|--help		- print this help and exit
	-x|--debug		- enable debug mode, raise output level
	-d|--dont-recalc	- do not recalculate test times / operations count even if memory of execution time limits are low
	-D|--dumb-test-print	- print dumb test time, for debug purpose
	-L|--list-tests		- output list of available tests and exit
	-I|--system-info	- output system info but do not run tests and exit
	-m|--memory-limit <Mb>	- set memory_limit value in Mb, defaults to 130 (Mb)
	-t|--time-limit <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)
	-T|--run-test <name>	- run selected test, test names from --list-tests output, can be defined multiple times
```
Например: `php bench.php -m=64 -t=30`

Второй вариант передачи значений для параметров - переменные окружения:
```
env PHP_MEMORY_LIMIT=64 PHP_TIME_LIMIT=30 php bench.php
```

Доступные переменные:

- PHP_TIME_LIMIT=<Секунды>
- PHP_DEBUG_MODE=0/1
- PHP_MEMORY_LIMIT=<Мб>
- DONT_RECALCULATE_LIMITS=0/1
- LIST_TESTS=0/1
- SYSTEM_INFO=0/1
- RUN_TESTS=test1,test2,...

#### Дополнительно (утилиты в Linux)

- Вы можете установить приоритет процесса с помощью команды `nice` - от -20 (высокий) до 19 (низкий). Пример, приоритет 5: `nice -5 php bench.php`. Смотрите `man nice`.
- Вы можете установить приоритет ввода-вывода с помощью команды `ionice`. Пример: `ionice -c3 php bench.php`. Смотрите `man ionice`.
- Вы можете привязать выполнение скрипта к ядру процессора с помощью команды `taskset`. Пример: `taskset -c -p 0 php bench.php`. Смотрите `man taskset`.
- Вы можете комбинировать команды: `taskset -c -p 0 nice -10 ionice -c3 php bench.php`.

### 2. Через веб-сервера (apache + php)

Просто положите в любую доступную для выполнения php директорию сайта, например в корень.

Потом скрипт можно будет вызывать с параметрами, как из консоли:
`curl http://www.example.com/bench.php?memory_limit=64&time_limit=30`
или через браузер.

Доступные параметры:

- time_limit=Секунды
- debug_mode=0/1
- memory_limit=Мб
- dont_recalculate_limits=0/1
- list_tests=0/1
- system_info=0/1
- run_tests=test1,test2,...

### Учет параметров хостинга

На многих хостингах параметры `memory_limit` и `max_execution_time` могут быть жестко зафиксированы.

В этом случае скрипт не сможет установить переданные в него значения параметров,
по крайней мере не выше лимитов.

Пересчет времени выполнения скрипта будет произведен по наименьшим результирующим значениям.

### Другие платформы

Например, на Raspberry Pi 2B, 3B и других подобных платах скорость выполнения настолько медленная,
что приходится указвать параметры `-d -t 3600`, чтобы прошли все тесты.

Это касается всех ARM, MIPS и т.п. А так же старых процессоров AMD и Intel, как Celeron, Atom, Duron и т.п.

## Пример вывода скрипта

```
-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2021-12-19 18:22:48
Server              : Linux/5.4.0-91-lowlatency x86_64
Platform            : Linux
System              : Ubuntu 18.04.6 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-6600K CPU @ 3.50GHz
              cores : 4
          available : 4
                MHz : 3795.056 MHz
Benchmark version   : 1.0.45
PHP version         : 8.0.12-SergeyD/2
PHP time limit      : 0 sec
Setup time limit    : 600 sec
PHP memory limit    : 128M
Setup memory limit  : 130 Mb
Crypt hash algo     : MD5
     Loaded modules
          -useful->
               json : yes
           mbstring : yes; func_overload: 0
               pcre : yes; version: 10.39 2021-10-29
          simplexml : yes; libxml version: 2.9.4
                dom : yes
               intl : yes; icu version: 60.2
       -affecting->
            opcache : yes; enabled: 0
             xcache : no; enabled: 0
                apc : no; enabled: 0
       eaccelerator : no; enabled: 0
             xdebug : no
     PHP parameters
       open_basedir : is empty? yes
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    2.854 sec | 700.80 kOp/s | 184.66  Ops/MHz |      2 Mb
02_string_concat               :    1.682 sec |  14.86 MOp/s |   3.92 kOps/MHz |  89.83 Mb
03_1_string_number_concat      :    1.641 sec |   3.05 MOp/s | 802.89  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.473 sec |   3.39 MOp/s | 894.53  Ops/MHz |      4 Mb
04_string_simple_functions     :    1.282 sec |   1.01 MOp/s | 267.26  Ops/MHz |      4 Mb
05_string_multibyte            :    1.035 sec | 125.65 kOp/s |  33.11  Ops/MHz |      4 Mb
06_string_manipulation         :    2.476 sec | 524.98 kOp/s | 138.33  Ops/MHz |      4 Mb
07_regex                       :    2.097 sec | 619.97 kOp/s | 163.36  Ops/MHz |      4 Mb
08_1_hashing                   :    1.919 sec | 677.59 kOp/s | 178.54  Ops/MHz |      4 Mb
08_2_crypt                     :    8.188 sec |   1.22 kOp/s |   0.32  Ops/MHz |      4 Mb
09_json_encode                 :    2.116 sec | 614.45 kOp/s | 161.91  Ops/MHz |      4 Mb
10_json_decode                 :    3.263 sec | 398.42 kOp/s | 104.98  Ops/MHz |      4 Mb
11_serialize                   :    1.524 sec | 853.09 kOp/s | 224.79  Ops/MHz |      4 Mb
12_unserialize                 :    1.577 sec | 824.50 kOp/s | 217.26  Ops/MHz |      4 Mb
13_array_fill                  :    3.598 sec |  25.01 MOp/s |   6.59 kOps/MHz |     24 Mb
14_array_range                 :    1.881 sec |  79.76 kOp/s |  21.02  Ops/MHz |     24 Mb
14_array_unset                 :    2.790 sec |  32.26 MOp/s |   8.50 kOps/MHz |     24 Mb
15_clean_loops                 :    1.280 sec | 312.46 MOp/s |  82.33 kOps/MHz |      4 Mb
16_loop_ifelse                 :    1.876 sec |  53.30 MOp/s |  14.05 kOps/MHz |      4 Mb
17_loop_ternary                :    3.019 sec |  33.13 MOp/s |   8.73 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    1.102 sec |  45.37 MOp/s |  11.95 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    4.448 sec |  11.24 MOp/s |   2.96 kOps/MHz |      4 Mb
19_type_functions              :    1.156 sec |   3.46 MOp/s | 911.66  Ops/MHz |      4 Mb
20_type_casting                :    1.159 sec |   3.45 MOp/s | 909.48  Ops/MHz |      4 Mb
21_0_loop_exception_none       :    0.197 sec |  50.75 MOp/s |  13.37 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.203 sec |  49.22 MOp/s |  12.97 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    2.878 sec |   3.47 MOp/s | 915.66  Ops/MHz |      4 Mb
22_loop_null_op                :    1.233 sec |  48.64 MOp/s |  12.82 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    1.175 sec |  51.06 MOp/s |  13.45 kOps/MHz |      4 Mb
26_1_class_public_properties   :    0.121 sec |  82.98 MOp/s |  21.87 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.397 sec |  25.20 MOp/s |   6.64 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    1.186 sec |   8.43 MOp/s |   2.22 kOps/MHz |      4 Mb
27_simplexml                   :    3.846 sec |  13.00 kOp/s |   3.43  Ops/MHz |      4 Mb
28_domxml                      :    4.018 sec |  12.44 kOp/s |   3.28  Ops/MHz |      4 Mb
29_datetime                    :    0.757 sec | 660.44 kOp/s | 174.03  Ops/MHz |      4 Mb
30_intl_number_format          :    0.986 sec |  20.28 kOp/s |   5.34  Ops/MHz |      4 Mb
31_intl_message_format         :    1.978 sec | 101.10 kOp/s |  26.64  Ops/MHz |      4 Mb
32_intl_calendar               :    0.786 sec | 381.78 kOp/s | 100.60  Ops/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :   75.195 sec |  14.85 MOp/s |   3.91 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :    86.54 Mb
```
