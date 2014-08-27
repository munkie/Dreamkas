Meta:
@sprint_40
@us_105

Narrative:
Как владелец,
Я хочу создавать, редактировать и удалять списания товаров в магазинах,
Чтобы остатки себестоимости товаров в учетной системе соответствовали действительности

Scenario: Валидация поле дата при создании возврата поставщику - пустое значение

GivenStories: precondition/товародвижение/возврат_поставщику/создать_магазин_с_ассортиментом.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 's40u105@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Вернуть поставщику на странице товародвижения
And пользователь* находится в модальном окне 'создания возврата поставщику'
And пользователь* в модальном окне вводит данные
| elementName | value |
| date |  |
| store | s40u105-store1 |
| product.name | s40u105-product1 |
| price | 150 |
| quantity | 5 |
And пользователь* нажимает на кнопку добавления нового товара

When пользователь* нажимает на кнопку создания 'Вернуть'

Then пользователь* проверяет, что у поля с именем 'date' имеется сообщения об ошибке с сообщением 'Заполните это поле'

Scenario: Валидация поле магазин при создании возврата поставщику - не выбрано значение

GivenStories: precondition/товародвижение/возврат_поставщику/создать_магазин_с_ассортиментом.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 's40u105@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Вернуть поставщику на странице товародвижения
And пользователь* находится в модальном окне 'создания возврата поставщику'
And пользователь* в модальном окне вводит данные
| elementName | value |
| date | 20.08.2014 |
| product.name | s40u105-product1 |
| price | 150 |
| quantity | 5 |
And пользователь* нажимает на кнопку создания 'Вернуть'

Then пользователь* проверяет, что у поля с именем 'store' имеется сообщения об ошибке с сообщением 'Заполните это поле'

Scenario: Валидация отсутствия продуктов при создании возврата поставщику

GivenStories: precondition/товародвижение/возврат_поставщику/создать_магазин_с_ассортиментом.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 's40u105@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Вернуть поставщику на странице товародвижения
And пользователь* находится в модальном окне 'создания возврата поставщику'
And пользователь* в модальном окне вводит данные
| elementName | value |
| date | 20.08.2014 |
| store | s40u105-store1 |

And пользователь* нажимает на кнопку создания 'Вернуть'

Then пользователь видит сообщение об ошибке 'Нужно добавить минимум один товар'