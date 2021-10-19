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
<<< WARNING >>>
CPU is in powersaving mode? Set CPU governor to 'performance'!
 Fire up CPU and recalculate MHz!
-------------------------------------------------------------------------------------------

-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2021-10-19 18:15:30
Server              : Linux/5.4.0-87-lowlatency x86_64
Platform            : Linux
System              : Ubuntu 18.04.6 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-6600K CPU @ 3.50GHz
              cores : 4
          available : 4
                MHz : 3313.1MHz
Benchmark version   : 1.0.40
PHP version         : 8.0.10-SergeyD/2
PHP time limit      : 0 sec
PHP memory limit    : 128M
Memory              : 256 Mb available
     loaded modules :
           mbstring : yes
               json : yes
               pcre : yes
            opcache : yes
             xdebug : no
Set time limit      : 600 sec
Crypt hash algo     : MD5
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    1.505 sec | 664.66 kOp/s | 200.62  Ops/MHz |      2 Mb
02_string_concat               :    0.244 sec |  31.59 MOp/s |   9.54 kOps/MHz | 128.84 Mb
03_1_string_number_concat      :    1.575 sec |   3.17 MOp/s | 957.92  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.400 sec |   3.57 MOp/s |   1.08 kOps/MHz |      4 Mb
04_string_simple_functions     :    1.373 sec | 946.94 kOp/s | 285.82  Ops/MHz |      4 Mb
05_string_multibyte            :    1.073 sec | 121.21 kOp/s |  36.58  Ops/MHz |      4 Mb
06_string_manipulation         :    2.567 sec | 506.39 kOp/s | 152.85  Ops/MHz |      4 Mb
07_regex                       :    1.990 sec | 653.12 kOp/s | 197.13  Ops/MHz |      4 Mb
08_1_hashing                   :    1.988 sec | 653.91 kOp/s | 197.37  Ops/MHz |      4 Mb
08_2_crypt                     :    8.556 sec |   1.17 kOp/s |   0.35  Ops/MHz |      4 Mb
09_json_encode                 :    2.289 sec | 567.84 kOp/s | 171.39  Ops/MHz |      4 Mb
10_json_decode                 :    3.430 sec | 379.00 kOp/s | 114.39  Ops/MHz |      4 Mb
11_serialize                   :    1.581 sec | 822.11 kOp/s | 248.14  Ops/MHz |      4 Mb
12_unserialize                 :    1.574 sec | 825.87 kOp/s | 249.27  Ops/MHz |      4 Mb
13_array_fill                  :    1.904 sec |  26.26 MOp/s |   7.93 kOps/MHz |     12 Mb
14_array_range                 :    0.575 sec | 173.80 kOp/s |  52.46  Ops/MHz |     12 Mb
14_array_unset                 :    1.565 sec |  31.95 MOp/s |   9.64 kOps/MHz |     12 Mb
15_loops                       :    0.675 sec | 296.18 MOp/s |  89.40 kOps/MHz |      4 Mb
16_loop_ifelse                 :    0.554 sec |  90.23 MOp/s |  27.23 kOps/MHz |      4 Mb
17_loop_ternary                :    1.339 sec |  37.34 MOp/s |  11.27 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    0.414 sec |  48.36 MOp/s |  14.60 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    2.755 sec |   7.26 MOp/s |   2.19 kOps/MHz |      4 Mb
19_type_functions              :    0.713 sec |   4.21 MOp/s |   1.27 kOps/MHz |      4 Mb
20_type_conversion             :    0.711 sec |   4.22 MOp/s |   1.27 kOps/MHz |      4 Mb
21_0_loop_exception_none       :    0.022 sec | 181.94 MOp/s |  54.92 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.027 sec | 147.28 MOp/s |  44.45 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    1.120 sec |   3.57 MOp/s |   1.08 kOps/MHz |      4 Mb
22_loop_null_op                :    1.069 sec |  46.78 MOp/s |  14.12 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    0.993 sec |  50.36 MOp/s |  15.20 kOps/MHz |      4 Mb
26_1_class_public_properties   :    0.066 sec |  75.98 MOp/s |  22.93 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.214 sec |  23.38 MOp/s |   7.06 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    0.632 sec |   7.91 MOp/s |   2.39 kOps/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :   46.494 sec |  12.96 MOp/s |   3.91 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :   125.46 Mb
```
