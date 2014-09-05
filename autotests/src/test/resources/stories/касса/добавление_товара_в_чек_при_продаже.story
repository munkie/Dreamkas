Meta:
@sprint_41
@us_111.2

Narrative:
Как владелец,
Я хочу найти на кассе нужный товар и добавить его в чек,
Чтобы сформировать чек продажи

Scenario: Поиск товара в автокомплите не более трех симоволов

Meta:

Given пользователь запускает консольную команду для создания пользователя с параметрами: адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'
Given пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает магазин с именем 'store-s41u1111' и адресом 'адрес'
Given пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает группу с именем 'pos-group1'
And пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает продукт с именем 'pos-product1', еденицами измерения 'шт.', штрихкодом 'post-barcode-1', НДС '0', ценой закупки '100' и ценой продажи '110' в группе с именем 'pos-group1'

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение value в поле с именем 'autocomplete'

Then пользователь ждет пока загрузится простой прелоадер
And пользователь проверяет, что на странице присутствует текст 'Для поиска товара введите 3 или более символа.'

Examples:
| value |
| ! |
| !p |
| !po |

Scenario: Поиск товара начинается если вводим более трех символов в поле автокомплита

Meta:

Given the user runs the symfony:env:init command
Given пользователь запускает консольную команду для создания пользователя с параметрами: адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'
Given пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает магазин с именем 'store-s41u1111' и адресом 'адрес'
Given пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает группу с именем 'pos-group1'
And пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает продукт с именем 'pos-product1', еденицами измерения 'шт.', штрихкодом 'post-barcode-1', НДС '0', ценой закупки '100' и ценой продажи '110' в группе с именем 'pos-group1'

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение value в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция результатов поиска автокомплита содержит следующие конкретные данные
| name | sku | barcode |
| pos-product1 | 10001 | post-barcode-1 |

Examples:
| value |
| !pos |
| !pos- |
| !pos-p |

Scenario: Проверка очистки поле автокомплита путем нажатия кнопки крестик

Meta:

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение '!pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция результатов поиска автокомплита содержит следующие конкретные данные
| name | sku | barcode |
| pos-product1 | 10001 | post-barcode-1 |

When пользователь* вводит значение '#clear' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция результатов поиска автокомплита пуста

Scenario: Поиск товара в автокомплите по имени

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение '!pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция результатов поиска автокомплита содержит следующие конкретные данные
| name | sku | barcode |
| pos-product1 | 10001 | post-barcode-1 |

Scenario: Поиск товара в автокомплите по локальному коду

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение '!10001' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция результатов поиска автокомплита содержит следующие конкретные данные
| name | sku | barcode |
| pos-product1 | 10001 | post-barcode-1 |

Scenario: Поиск товара в автокомплите по штрихкоду

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение '!post-barcode-1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция результатов поиска автокомплита содержит следующие конкретные данные
| name | sku | barcode |
| pos-product1 | 10001 | post-barcode-1 |

Scenario: Проверка сообщения если нет ни одного товара в чеке

Meta:

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

Then пользователь проверяет, что на странице присутствует текст 'Для поиска товара введите 3 или более символа.'

Scenario: После клика на айтем в автокомплитном поле, результаты не убирается

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |

Then пользователь проверяет, что коллекция результатов поиска автокомплита содержит следующие конкретные данные
| name | sku | barcode |
| pos-product1 | 10001 | post-barcode-1 |

Scenario: Добавление товара в чек

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |

Then пользователь проверяет, что чек получился на сумму '110,00'

Scenario: Пересчитывание суммы после добавление товара

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |

Then пользователь проверяет, что чек получился на сумму '110,00'

When пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |
| pos-product1 | 1,0 шт. | 110,00 |

Then пользователь проверяет, что чек получился на сумму '220,00'

Scenario: Проверка, что последний добавленный товар в чек всегда прикреплен к низу чека, при заполнение чека

Meta:
@smoke
@skip
@ignore

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story,
              precondition/касса/создание_большого_количества_товаров.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product2' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product3' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product4' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product5' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product6' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product7' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product8' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product9' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product10' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product11' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product12' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product13' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product14' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product15' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product16' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product17' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product18' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product19' в поле с именем 'autocomplete'
And пользователь* вводит значение 'pos-product20' в поле с именем 'autocomplete'

Then пользователь проверяет, что последний добавленный продукт в чек прикреплен к концу чека