
#### Результат выполнения следующего тестового задания:

```
Есть продукты A, B, C, D, E, F, G, H, I, J, K, L, M. Каждый продукт стоит определенную сумму.

Есть набор правил расчета итоговой суммы:
1. Если одновременно выбраны А и B, то их суммарная стоимость уменьшается на 10% (для каждой пары А и B)
2. Если одновременно выбраны D и E, то их суммарная стоимость уменьшается на 6% (для каждой пары D и E)
3. Если одновременно выбраны E, F, G, то их суммарная стоимость уменьшается на 3% (для каждой тройки E, F, G)
4. Если одновременно выбраны А и один из [K, L, M], то стоимость выбранного продукта уменьшается на 5%
5. Если пользователь выбрал одновременно 3 продукта, он получает скидку 5% от суммы заказа
6. Если пользователь выбрал одновременно 4 продукта, он получает скидку 10% от суммы заказа
7. Если пользователь выбрал одновременно 5 продуктов, он получает скидку 20% от суммы заказа
8. Описанные скидки 5,6,7 не суммируются, применяется только одна из них
9. Продукты A и C не участвуют в скидках 5,6,7
10. Каждый товар может участвовать только в одной скидке. Скидки применяются последовательно в порядке описанном выше.

 
Обязательные требования:
Необходимо написать программу на PHP, которая, имея на входе набор продуктов (один продукт может встречаться несколько раз) рассчитывала суммарную их стоимость.
Программу необходимо написать максимально просто и максимально гибко. Учесть, что список продуктов практически не будет меняться, также как и типы скидок. В то время как правила скидок (какие типы скидок к каким продуктам) будут меняться регулярно.


Все параметры задаются в программе статически (пользовательский ввод обрабатывать не нужно).
Оценивается подход к решению задачи.
Тщательное тестирование решения проводить не требуется.
Скрипты обязательно должны выполнять принципы SOLID.
Необходимо использовать стандарты кодирования PSR

```

Уточнения относительно задания:
 - п.4, если присутствуют товары A +  K и M, на какой [K, M] начислять скидку?  
 Решил начислять на первый найденный, в порядке перечисления.
 - п.5-7, если одновременно выбрал N одинаковых/разных/суммарно?   
 Решил считать суммарно, независимо от того, одинаковые товары или разные

Из описания задачи я выделил 3 схемы применения скидки:
 - скидка на группу товаров из перечисленных (пункты 1, 2, 3)
 - скидка на один товар из перечисленных, взятый в дополнение к основному товару (пункт 4)
 - скидка на группу товаров, на основании их количества, с возможностью исключения перечисленных (пункты 5, 6, 7)

### Нюансы реализации

1. `Product::$title` рассмотрен в качестве ключа, для упрощения.   
Подразумевается, что все объекты `Product` с одинаковыми названиями будут идентичны, 
т.е. кейс, когда два товара "A" имеют разную цену, в данном случае, не предусмотрен.
Для для того, чтобы предусмотреть вариант с различиями, в коллекции `ProductCollection` нужно было бы использовать `SplObjectStorage` вместо массива, 
соответственно, немного усложнился бы поиск эелемнта по названию, 
или потребовалось бы в конфигурации стратерий скидок работать с объектами `Product`, а не названиями.

2. **BCMath не использовался умышленно**. 
Использование данного расширения усложнило бы чтение и восприятие кода, а также заставило бы повсеместно использовать строки для передачи вещественных чисел.
В продакшене, для обеспечения точности, следовало бы использовать `BCMath` или `GMP`

3. Опущена валидация входных данных. 
Т.е. передача, к примеру, отрицательного значения в один из методов, вероятно, приведет к некорректной работе приложения.

4. Конфигурация стратегий примитивная. 
Можно было бы сделать конфигурацию более гибкой, возможно, подключить парсер какого-либо формата конфигов и сделать более человекочитаемую настройку, 
но ввиду отсутствия времени на эксперименты, а таже требования (`максимально просто и максимально гибко`), решил не заморачиваться.  
Конструкторы вызывают метод addRules только для того, чтобы нельзя было создать стратегию с пустым конфигом.  

5. Тесты всех стратегий умышленно собрал в один файл, так же как и не писал комментарии.  
Это не production-ready код, третить время и тщательно вылизывать его не вижу смысла.  
Тесты мне нужны были, в первую очередь, чтобы убедиться, что все работает.

#### Пример использования
```php
<?php

use lexeo\testonlnc\Discount\DiscountCalculator;
use lexeo\testonlnc\Discount\Strategy\AdditionalProductStrategy;
use lexeo\testonlnc\Discount\Strategy\ProductNumberStrategy;
use lexeo\testonlnc\Discount\Strategy\ProductGroupStrategy;
use lexeo\testonlnc\ProductCollection;
use lexeo\testonlnc\Product;

$discountCalculator = new DiscountCalculator(
    (new ProductGroupStrategy(['A', 'B'], 10))
        ->addRule(['D', 'E'], 6)
        ->addRule(['E', 'F', 'G'], 3),

    (new AdditionalProductStrategy('A', ['K', 'L', 'M'], 5)), 

    (new ProductNumberStrategy(3, 5))
        ->addRule(4, 10)
        ->addRule(5, 20)
        ->excludeProducts(['A', 'C'])
);
 
$collection = new ProductCollection();
$collection->addProduct(new Product('A', 11.99));
// add more products

$discount = $discountCalculator->calculateDiscount($collection);

```