Meta:
@sprint_41
@us_113.2

Narrative:
Как владелец,
Я хочу при первом старте приложения Dreamkas на android выбрать магазин,
Чтобы все кассовые операции фиксировались для этого магазина

Scenario: Выбор магазина при первом старте

Meta:

Given пользователь авторизируется в системе используя адрес электронной почты 'owner@lighthouse.pro' и пароль 'lighthouse'

When пользователь выбирает магазин с именем 'Магазин №1' из списка
And пользователь нажимает на кнопку 'Перейти к кассе'

Then пользователь проверяет, что заголовок 'Магазин №1'

Scenario: Магазин выбирается автоматически при старте если уже был выбран заранее

Meta:

Given пользователь авторизируется в системе используя адрес электронной почты 'owner@lighthouse.pro' и пароль 'lighthouse'

When пользователь выбирает магазин с именем 'Магазин №1' из списка
And пользователь нажимает на кнопку 'Перейти к кассе'

Then пользователь проверяет, что заголовок 'Магазин №1'

When пользователь закрывает приложение
And пользователь открывает приложение

When пользователь авторизируется в системе используя адрес электронной почты 'owner@lighthouse.pro' и пароль 'lighthouse'

Then пользователь проверяет, что заголовок 'Магазин №1'

Scenario: Смена магазина

Meta:

Given пользователь авторизируется в системе используя адрес электронной почты 'owner@lighthouse.pro' и пароль 'lighthouse'

When пользователь выбирает магазин с именем 'Магазин №1' из списка
And пользователь нажимает на кнопку 'Перейти к кассе'

Then пользователь проверяет, что заголовок 'Магазин №1'

When пользователь открывает боковое меню и нажимает на элемент 'Сменить магазин'

When пользователь выбирает магазин с именем 'Магазин №2' из списка
And пользователь нажимает на кнопку 'Перейти к кассе'

Then пользователь проверяет, что заголовок 'Магазин №2'