# Простой скрипт проверки быстродействия PHP

Работает со всеми версиями ПХП: от 4.3 до 8.1

## Зависимости

Необходимые модули для полного тестирования php:

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

### Модули, влияющие на результаты тестов

- xdebug - может замедлить работу большей части тестов в два раза, а некоторых, связанных с обработкой ошибок - в 10!
- opcache - может закешировать выполнение функций, или выкинуть "не влияющие на выполнение" куски кода
- другие ускорители оп-кода: xcache, apc, eaccelerator и т.п.

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
<<< WARNING >>> You need to disable Xdebug extension! It greatly slow things down! And mess with PHP internals.
<<< WARNING >>> Execution time limit not droppped to '600' seconds!
Script will have only '0' seconds to run.
<<< WARNING >>> Extension 'xdebug' loaded! It will affect results and slow things greatly! Even if not enabled!
<<< WARNING >>> Set xdebug.mode in php.ini / VHost or FPM config / php_admin_value or via cmd '-dxdebug.mode=off' option of PHP executable.

-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2022-05-03 18:22:49
Server              : Linux/5.4.0-104-lowlatency x86_64
Platform            : Linux
System              : Ubuntu 18.04.6 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-6600K CPU @ 3.50GHz
              cores : 4
          available : 4
                MHz : 3600 MHz
Benchmark version   : 1.0.47
PHP version         : 7.4.29-SergeyD/6.1
PHP time limit      : 0 sec
Setup time limit    : 600 sec
PHP memory limit    : 128M
Setup memory limit  : 130 Mb
Crypt hash algo     : MD5
     Loaded modules
          -useful->
               json : yes
           mbstring : yes;
               pcre : yes; version: 10.39 2021-10-29
          simplexml : yes; libxml version: 2.9.4
                dom : yes
               intl : yes; icu version: 66.1
       -affecting->
            opcache : yes; enabled: 0
             xcache : no; enabled: 0
                apc : no; enabled: 0
       eaccelerator : no; enabled: 0
             xdebug : yes, enabled: 1, mode: 'develop'
     PHP parameters
       open_basedir : is empty? yes
   mb.func_overload : 0
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    2.958 sec | 676.22 kOp/s | 178.48  Ops/MHz |      4 Mb
02_string_concat               :    1.683 sec |  14.86 MOp/s |   3.92 kOps/MHz |  89.83 Mb
03_1_string_number_concat      :    1.544 sec |   3.24 MOp/s | 854.83  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.348 sec |   3.71 MOp/s | 979.33  Ops/MHz |      4 Mb
04_string_simple_functions     :    1.320 sec | 984.64 kOp/s | 259.88  Ops/MHz |      4 Mb
05_string_multibyte            :    1.061 sec | 122.47 kOp/s |  32.32  Ops/MHz |      4 Mb
06_string_manipulation         :    2.397 sec | 542.37 kOp/s | 143.15  Ops/MHz |      4 Mb
07_regex                       :    2.035 sec | 638.84 kOp/s | 168.61  Ops/MHz |      4 Mb
08_1_hashing                   :    2.030 sec | 640.31 kOp/s | 169.00  Ops/MHz |      4 Mb
08_2_crypt                     :    8.698 sec |   1.15 kOp/s |   0.30  Ops/MHz |      4 Mb
09_json_encode                 :    2.322 sec | 559.91 kOp/s | 147.78  Ops/MHz |      4 Mb
10_json_decode                 :    3.556 sec | 365.54 kOp/s |  96.48  Ops/MHz |      4 Mb
11_serialize                   :    1.551 sec | 838.30 kOp/s | 221.25  Ops/MHz |      4 Mb
12_unserialize                 :    1.677 sec | 774.97 kOp/s | 204.54  Ops/MHz |      4 Mb
13_array_fill                  :    3.740 sec |  24.07 MOp/s |   6.35 kOps/MHz |     24 Mb
14_array_range                 :    2.007 sec |  74.74 kOp/s |  19.73  Ops/MHz |     24 Mb
14_array_unset                 :    2.833 sec |  31.77 MOp/s |   8.38 kOps/MHz |     24 Mb
15_clean_loops                 :    1.342 sec | 298.14 MOp/s |  78.69 kOps/MHz |      4 Mb
16_loop_ifelse                 :    1.992 sec |  50.20 MOp/s |  13.25 kOps/MHz |      4 Mb
17_loop_ternary                :    3.057 sec |  32.71 MOp/s |   8.63 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    1.017 sec |  49.15 MOp/s |  12.97 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    4.729 sec |  10.57 MOp/s |   2.79 kOps/MHz |      4 Mb
19_type_functions              :    1.152 sec |   3.47 MOp/s | 916.65  Ops/MHz |      4 Mb
20_type_casting                :    1.178 sec |   3.39 MOp/s | 895.86  Ops/MHz |      4 Mb
21_0_loop_exception_none       :    0.204 sec |  48.94 MOp/s |  12.92 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.212 sec |  47.21 MOp/s |  12.46 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    3.214 sec |   3.11 MOp/s | 821.23  Ops/MHz |      4 Mb
22_loop_null_op                :    1.266 sec |  47.41 MOp/s |  12.51 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    1.202 sec |  49.93 MOp/s |  13.18 kOps/MHz |      4 Mb
26_1_class_public_properties   :    0.133 sec |  75.10 MOp/s |  19.82 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.425 sec |  23.54 MOp/s |   6.21 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    1.189 sec |   8.41 MOp/s |   2.22 kOps/MHz |      4 Mb
27_simplexml                   :    4.121 sec |  12.13 kOp/s |   3.20  Ops/MHz |      4 Mb
28_domxml                      :    4.228 sec |  11.83 kOp/s |   3.12  Ops/MHz |      4 Mb
29_datetime                    :    0.571 sec | 875.87 kOp/s | 231.17  Ops/MHz |      4 Mb
30_intl_number_format          :    0.826 sec |  24.22 kOp/s |   6.39  Ops/MHz |      4 Mb
31_intl_message_format         :    4.236 sec |  47.22 kOp/s |  12.46  Ops/MHz |      4 Mb
32_intl_calendar               :    0.844 sec | 355.34 kOp/s |  93.79  Ops/MHz |      4 Mb
33_phpinfo_generate            :    1.440 sec |   6.95 kOp/s |   1.83  Ops/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :   81.337 sec |  13.73 MOp/s |   3.62 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :    86.58 Mb
```
