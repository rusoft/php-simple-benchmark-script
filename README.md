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
<pre>
<<< WARNING >>>
CPU is in powersaving mode? Set CPU governor to 'performance'!
 Fire up CPU and recalculate MHz!
</pre>
<pre>
-------------------------------------------------------------------------------------------
|                                  PHP BENCHMARK SCRIPT                                   |
-------------------------------------------------------------------------------------------
Start               : 2020-11-26 17:53:17
Server              : Linux/4.4.0-194-generic x86_64
Platform            : Linux
System              : Ubuntu 16.04.7 LTS
CPU                 :
              model : Intel(R) Core(TM) i5-2300 CPU @ 2.80GHz
              cores : 4
          available : 4
                MHz : 3098.156MHz
Memory              : 256 Mb available
Benchmark version   : 1.0.37
PHP version         : 8.0.0-Rusoft/1.1
  available modules :
           mbstring : yes
               json : yes
               pcre : yes
Max execution time  : 600 sec
Crypt hash algo     : MD5
-------------------------------------------------------------------------------------------
TEST NAME                      :      SECONDS |       OP/SEC |      OP/SEC/MHz |    MEMORY
-------------------------------------------------------------------------------------------
01_math                        :    2.094 sec | 477.61 kOp/s | 154.16  Ops/MHz |      2 Mb
02_string_concat               :    0.310 sec |  24.85 MOp/s |   8.02 kOps/MHz | 128.84 Mb
03_1_string_number_concat      :    2.045 sec |   2.44 MOp/s | 789.02  Ops/MHz |      4 Mb
03_2_string_number_format      :    1.791 sec |   2.79 MOp/s | 901.26  Ops/MHz |      4 Mb
04_string_simple_functions     :    1.800 sec | 722.30 kOp/s | 233.14  Ops/MHz |      4 Mb
05_string_multibyte            :    1.482 sec |  87.73 kOp/s |  28.32  Ops/MHz |      4 Mb
06_string_manipulation         :    4.460 sec | 291.46 kOp/s |  94.07  Ops/MHz |      4 Mb
07_regex                       :    2.690 sec | 483.20 kOp/s | 155.96  Ops/MHz |      4 Mb
08_1_hashing                   :    2.880 sec | 451.43 kOp/s | 145.71  Ops/MHz |      4 Mb
08_2_crypt                     :   10.627 sec | 940.96  Op/s |   0.30  Ops/MHz |      4 Mb
09_json_encode                 :    3.451 sec | 376.74 kOp/s | 121.60  Ops/MHz |      4 Mb
10_json_decode                 :    5.189 sec | 250.52 kOp/s |  80.86  Ops/MHz |      4 Mb
11_serialize                   :    2.432 sec | 534.50 kOp/s | 172.52  Ops/MHz |      4 Mb
12_unserialize                 :    2.348 sec | 553.61 kOp/s | 178.69  Ops/MHz |      4 Mb
13_array_fill                  :    2.561 sec |  19.52 MOp/s |   6.30 kOps/MHz |     12 Mb
14_array_range                 :    0.690 sec | 145.02 kOp/s |  46.81  Ops/MHz |     12 Mb
14_array_unset                 :    2.130 sec |  23.48 MOp/s |   7.58 kOps/MHz |     12 Mb
15_loops                       :    0.828 sec | 241.67 MOp/s |  78.00 kOps/MHz |      4 Mb
16_loop_ifelse                 :    1.430 sec |  34.98 MOp/s |  11.29 kOps/MHz |      4 Mb
17_loop_ternary                :    2.315 sec |  21.59 MOp/s |   6.97 kOps/MHz |      4 Mb
18_1_loop_defined_access       :    0.588 sec |  34.02 MOp/s |  10.98 kOps/MHz |      4 Mb
18_2_loop_undefined_access     :    3.948 sec |   5.07 MOp/s |   1.64 kOps/MHz |      4 Mb
19_type_functions              :    1.044 sec |   2.87 MOp/s | 927.08  Ops/MHz |      4 Mb
20_type_conversion             :    1.045 sec |   2.87 MOp/s | 926.30  Ops/MHz |      4 Mb
21_0_loop_exception_none       :    0.035 sec | 115.49 MOp/s |  37.28 kOps/MHz |      4 Mb
21_1_loop_exception_try        :    0.034 sec | 116.96 MOp/s |  37.75 kOps/MHz |      4 Mb
21_2_loop_exception_catch      :    1.676 sec |   2.39 MOp/s | 770.23  Ops/MHz |      4 Mb
22_loop_null_op                :    1.593 sec |  31.38 MOp/s |  10.13 kOps/MHz |      4 Mb
23_loop_spaceship_op           :    1.473 sec |  33.95 MOp/s |  10.96 kOps/MHz |      4 Mb
26_1_class_public_properties   :    0.092 sec |  54.39 MOp/s |  17.56 kOps/MHz |      4 Mb
26_2_class_getter_setter       :    0.311 sec |  16.06 MOp/s |   5.18 kOps/MHz |      4 Mb
26_3_class_magic_methods       :    1.030 sec |   4.85 MOp/s |   1.57 kOps/MHz |      4 Mb
-------------------------------------------------------------------------------------------
Total time:                    :   66.423 sec |   9.07 MOp/s |   2.93 kOps/MHz |
Current PHP memory usage:      :        4 Mb
Peak PHP memory usage:         :   125.44 Mb
</pre>
```
