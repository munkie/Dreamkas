Meta:
@smoke
@precondition
@sprint_43
@us_111.5

Scenario: Cоздание списания для списка отчета остатка товаров

Given пользователь создает апи объект списания с датой '19.08.2014', магазином с именем 'store-reports'
And пользователь добавляет к апи объекту списания продукт с именем 'reports-product1', ценой '11.99', количеством '2' и причиной 'Бой'
And пользователь c электронным адресом 'reports@lighthouse.pro' сохраняет апи объект списания