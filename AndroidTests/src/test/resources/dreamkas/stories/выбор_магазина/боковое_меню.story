Scenario: Выход из кассы

Meta:

Meta:
@skip
@ignore

Given пользователь авторизируется в системе используя адрес электронной почты 'owner@lighthouse.pro' и пароль 'lighthouse'

When пользователь открывает боковое меню и нажимает на элемент 'Выйти'
And пользователь подтверждает выход и нажимает 'Да'

Then пользователь проверяет, что описание 'Добро пожаловать в DreamKas.'

Then пользователь проверяет, что текущая активити это 'ru.dreamkas.pos.view.LoginActivity_'
