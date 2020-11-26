# Простой скрипт проверки быстродействия PHP

Работает со всеми версиями ПХП: от 4.3 до 7.4

## Зависимости

Необходимы модули для php:

- pcre
- mbstring
- json
- xmlrpc

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
<pre>
<<< WARNING >>>
CPU is in powersaving mode? Set CPU governor to 'performance'!
 Fire up CPU and recalculate MHz!
</pre>
<pre>
-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2020-02-22 18:42:34
Server              : Linux/4.4.0-173-generic x86_64
Platform            : Linux
System              : Ubuntu 16.04.6 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-2300 CPU @ 2.80GHz
              cores : 4
          available : 4
                MHz : 3006.937MHz
Memory              : 256 Mb available
Benchmark version   : 1.0.36
PHP version         : 7.0.33-Rusoft/52
  available modules :
           mbstring : yes
               json : yes
             xmlrpc : yes
               pcre : yes
Max execution time  : 600 sec
Crypt hash algo     : MD5
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    3.200 sec | 312.51 kOp/s | 103.93  Ops/MHz |      4 Mb
02_string_concat               :    0.353 sec |  21.81 MOp/s |   7.25 kOps/MHz | 128.84 Mb
03_1_string_number_concat      :    2.262 sec |   2.21 MOp/s | 735.16  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.933 sec |   2.59 MOp/s | 860.05  Ops/MHz |      4 Mb
04_string_simple_functions     :    2.936 sec | 442.72 kOp/s | 147.23  Ops/MHz |      4 Mb
05_string_multibyte            :    9.490 sec |  13.70 kOp/s |   4.56  Ops/MHz |      4 Mb
06_string_manipulation         :    6.660 sec | 195.19 kOp/s |  64.91  Ops/MHz |      4 Mb
07_regex                       :    3.687 sec | 352.63 kOp/s | 117.27  Ops/MHz |      4 Mb
08_1_hashing                   :    4.216 sec | 308.37 kOp/s | 102.55  Ops/MHz |      4 Mb
08_2_crypt                     :   11.136 sec | 897.96  Op/s |   0.30  Ops/MHz |      4 Mb
09_json_encode                 :    4.262 sec | 305.02 kOp/s | 101.44  Ops/MHz |      4 Mb
10_json_decode                 :    6.374 sec | 203.94 kOp/s |  67.82  Ops/MHz |      4 Mb
11_serialize                   :    2.786 sec | 466.59 kOp/s | 155.17  Ops/MHz |      4 Mb
12_unserialize                 :    3.721 sec | 349.40 kOp/s | 116.20  Ops/MHz |      4 Mb
13_array_fill                  :    3.283 sec |  15.23 MOp/s |   5.06 kOps/MHz |     12 Mb
14_array_range                 :    0.827 sec | 120.89 kOp/s |  40.20  Ops/MHz |     12 Mb
14_array_unset                 :    2.642 sec |  18.93 MOp/s |   6.29 kOps/MHz |     12 Mb
15_loops                       :    1.418 sec | 141.08 MOp/s |  46.92 kOps/MHz |      4 Mb
16_loop_ifelse                 :    1.780 sec |  28.09 MOp/s |   9.34 kOps/MHz |      4 Mb
17_loop_ternary                :    2.905 sec |  17.21 MOp/s |   5.72 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    0.835 sec |  23.94 MOp/s |   7.96 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    7.404 sec |   2.70 MOp/s | 898.39  Ops/MHz |      4 Mb
19_type_functions              :    1.981 sec |   1.51 MOp/s | 503.61  Ops/MHz |      4 Mb
20_type_conversion             :    1.294 sec |   2.32 MOp/s | 771.25  Ops/MHz |      4 Mb
21_0_loop_exception_none       :    0.053 sec |  75.85 MOp/s |  25.22 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.058 sec |  69.18 MOp/s |  23.01 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    4.006 sec | 998.60 kOp/s | 332.10  Ops/MHz |      4 Mb
22_loop_null_op                :    1.966 sec |  25.43 MOp/s |   8.46 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    1.961 sec |  25.50 MOp/s |   8.48 kOps/MHz |      4 Mb
24_xmlrpc_encode               :    6.818 sec |  29.34 kOp/s |   9.76  Ops/MHz |      4 Mb
25_xmlrpc_decode               :    6.880 sec |   4.36 kOp/s |   1.45  Ops/MHz |      4 Mb
26_1_class_public_properties   :    0.163 sec |  30.70 MOp/s |  10.21 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.480 sec |  10.41 MOp/s |   3.46 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    1.448 sec |   3.45 MOp/s |   1.15 kOps/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :  111.218 sec |   5.42 MOp/s |   1.80 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :   125.46 Mb
</pre>
```
