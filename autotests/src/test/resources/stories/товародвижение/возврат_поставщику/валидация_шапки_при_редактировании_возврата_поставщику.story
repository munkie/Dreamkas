Meta:
@sprint_40
@us_105

Narrative:
Как владелец,
Я хочу создавать, редактировать и удалять возврат товаров поставщику в магазинах,
Чтобы остатки себестоимости товаров в учетной системе соответствовали действительности

Scenario: Валидация поле дата при редактировании оприходования - пустое значение

GivenStories: 
                precondition/товародвижение/возврат_поставщику/создать_магазин_с_ассортиментом.story,
                precondition/товародвижение/возврат_поставщику/создать_возврат_поставщику.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 's40u105@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на последнее созданный возврат поставщику с помощью конструктора оприходований на странице товародвижения

When пользователь* находится в модальном окне 'редактирования возврата поставщику'
And пользователь* в модальном окне вводит данные
| elementName | value |
| date | |
And пользователь* в модальном окне нажимает на кнопку сохранения

Then пользователь* проверяет, что у поля с именем 'date' имеется сообщения об ошибке с сообщением 'Заполните это поле'

Scenario: Валидация удаления всех продуктов из оприходования при редактировании

GivenStories: 
                precondition/товародвижение/возврат_поставщику/создать_магазин_с_ассортиментом.story,
                precondition/товародвижение/возврат_поставщику/создать_возврат_поставщику.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 's40u105@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на последнее созданный возврат поставщику с помощью конструктора оприходований на странице товародвижения
And пользователь* находится в модальном окне 'редактирования возврата поставщику'

When пользователь* в модальном окне товародвижения удаляет товар с названием 's40u105-product1'
Then пользователь* в модальном окне товародвижения проверяет, что количество продуктов равно '0'

When пользователь* в модальном окне нажимает на кнопку сохранения

Then пользователь видит сообщение об ошибке 'Нужно добавить минимум один товар'