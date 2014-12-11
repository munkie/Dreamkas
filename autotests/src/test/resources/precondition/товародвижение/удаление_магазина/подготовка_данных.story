Meta:
@precondition

Scenario: Создание магазина и проведение операций по нему

Given пользователь с адресом электронной почты 'user@lighthouse.pro' создает группу с именем 'user-group1'
And пользователь с адресом электронной почты 'user@lighthouse.pro' создает продукт с именем 'user-product1', еденицами измерения 'шт.', штрихкодом 'post-barcode-1', НДС '0', ценой закупки '100' и ценой продажи '110' в группе с именем 'user-group1'

Given пользователь с адресом электронной почты 'user@lighthouse.pro' создает магазин с именем 'store-user-delete' и адресом 'адрес'
Given пользователь с адресом электронной почты 'user@lighthouse.pro' создает поставщика с именем 'user-supplier-delete', адресом 'address', телефоном 'phone', почтой 'email' и контактным лицом 'contactPerson'

Given пользователь создает апи объект накладной с датой 'todayDate-5', статусом Оплачено 'false', магазином с именем 'store-user-delete', поставщиком с именем 'user-supplier-delete'
And пользователь добавляет продукт с именем 'user-product1', ценой '150' и количеством '1' к апи объекту накладной
And пользователь с адресом электронной почты 'user@lighthouse.pro' создает накладную через конструктор накладных

Given пользователь создает чек c датой 'saleTodayDate-4'
And пользователь добавляет товар в чек с именем 'user-product1', количеством '1' и по цене '120'
And пользователь вносит наличные в размере '500' рублей
And пользователь с адресом электронной почты 'user@lighthouse.pro' в магазине с именем 'store-user-delete' совершает продажу по созданному чеку
