Meta:
@release_0.44
@updated_email_sender_name
@us_123

Narrative:
Как владелец,
Я хочу восстановить забытый пароль к моей учётной записи LH,
Чтобы продолжить пользоваться системой.


Scenario: Востановление забытого пароля

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story

Given пользователь запускает консольную команду для создания пользователя с параметрами: адрес электронной почты 'autotests@lighthouse.pro' и пароль 'A1234567a'
And пользователь удаляет все сообщения в ящике

Given пользователь открывает стартовую страницу авторизации
Then пользователь ждет пока загрузится страница

When пользователь нажимает на ссылку востановления пароля
And пользователь вводит 'autotests@lighthouse.pro' в поле email на старанице восстановления пароля
And пользователь нажимает кнопку востановления пароля

Then пользователь видит текст над формой логина 'Пароль от аккаунта выслан Вам на почту.'
And пользователь видит что в форме уже заполнены поля на странице авторизации
| elementName | value |
| email | autotests@lighthouse.pro |

When пользователь получает последнее сообщение из тестового email из папки входящие

Then пользователь видит что сообщение пришло от '"Dreamkas.ru" <noreply@dreamkas.ru>'
And пользователь видит что заголовок сообщения содержит текст 'Восстановление пароля в Dreamkas'
And пользовател видит что письмо соответствует шаблону письма о востановлении пароля

When пользователь вводит пароль полученный из письма о восстановлении пароля
And пользовател жмёт кнопку авторизироваться и авторизируется

Then пользователь видит что он авторизирован как 'autotests@lighthouse.pro'

Scenario: Валидация поля восстановления пароля пустым значением

Meta:

Given пользователь открывает страницу восстановления пароля

When пользователь вводит '' в поле email на старанице восстановления пароля
And пользователь нажимает кнопку востановления пароля

Then пользователь видит сообщение об ошибке 'Заполните это поле'

Scenario: Восстановление несуществующего адреса электронной почты

Meta:

Given пользователь открывает страницу восстановления пароля

When пользователь вводит 'lighthouse@lighthouse.lighthouse' в поле email на старанице восстановления пароля
And пользователь нажимает кнопку востановления пароля

Then пользователь видит сообщение об ошибке 'Пользователь с таким e-mail не зарегистрирован в системе'

Scenario: Проверка заголовка текста страницы восстановления пароля

Meta:

Given пользователь открывает страницу восстановления пароля

Then пользователь проверяет, что заголовок страницы восстановления пароля равен 'ВОССТАНОВЛЕНИЕ ПАРОЛЯ'
