Meta:
@smoke
@sprint_40
@us_104

Scenario: Cценарий для создания списания

Given пользователь создает апи объект списания с датой '19.08.2014', магазином с именем 's40u104-store'
And пользователь добавляет к апи объекту списания продукт с именем 's40u104-product1', ценой '11.99', количеством '2' и причиной 'Бой'
And пользователь c электронным адресом 's40u104@lighthouse.pro' сохраняет апи объект списания