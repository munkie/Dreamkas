Meta:
@release_0.45
@us_121

Narrative:
Как владелец торговой точки,
Я хочу изменить свой пароль учётной записи Дримкас,
Чтобы сохранить контроль над аккаунтом Дримкас

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story

Scenario: Попытка отправки пустых полей страницы сменя пользователя

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль |  |
| Новый пароль |  |
| Подтверждение пароля |  |

Then пользователь проверяет, что у элемента с именем 'кнопка сохранения нового пароля' аттрибут 'disabled' имеет значение 'true'

Scenario: Попытка отправки пустого поля Старый пароль

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль |  |
| Новый пароль | newlighthouse |
| Подтверждение пароля | newlighthouse |

Then пользователь проверяет, что у элемента с именем 'кнопка сохранения нового пароля' аттрибут 'disabled' имеет значение 'true'

Scenario: Попытка отправки пустого поля Новый пароль

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse |
| Новый пароль |  |
| Подтверждение пароля | newlighthouse |

Then пользователь проверяет, что у элемента с именем 'кнопка сохранения нового пароля' аттрибут 'disabled' имеет значение 'true'

Scenario: Попытка отправки пустого поля Подтвердите новый пароль

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse |
| Новый пароль | newlighthouse |
| Подтверждение пароля |  |

Then пользователь проверяет, что у элемента с именем 'кнопка сохранения нового пароля' аттрибут 'disabled' имеет значение 'true'

Scenario: Старый пароль неверен

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse1234 |
| Новый пароль | newlighthouse |
| Подтверждение пароля | newlighthouse |

And пользователь* нажимает на елемент с именем 'кнопка сохранения нового пароля'

Then пользователь видит сообщение об ошибке 'Неверный пароль'

Scenario: Новый пароль не соответствует подтверждению

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse |
| Новый пароль | newlighthouse123 |
| Подтверждение пароля | newlighthouse |

And пользователь* нажимает на елемент с именем 'кнопка сохранения нового пароля'

Then пользователь видит сообщение об ошибке 'Пароли отличаются'

Scenario: Подтверждение не соответствует новому паролю

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse |
| Новый пароль | newlighthouse |
| Подтверждение пароля | newlighthouse123 |

And пользователь* нажимает на елемент с именем 'кнопка сохранения нового пароля'

Then пользователь видит сообщение об ошибке 'Пароли отличаются'

Scenario: Новый пароль совпадает c адресом электронной почты

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse |
| Новый пароль | user@lighthouse.pro |
| Подтверждение пароля | user@lighthouse.pro |

And пользователь* нажимает на елемент с именем 'кнопка сохранения нового пароля'

Then пользователь видит сообщение об ошибке 'E-mail и пароль не должны совпадать'

Scenario: Новый пароль должен быть больше 6 символов

Meta:

GivenStories: precondition/пользователь/создание_юзера.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

Given пользователь* взаимодействует со страницей 'настроек пользователя'
And пользователь* открывает страницу

When пользователь* вводит данные в поля
| elementName | value |
| Старый пароль | lighthouse |
| Новый пароль | 12345 |
| Подтверждение пароля | 12345 |

And пользователь* нажимает на елемент с именем 'кнопка сохранения нового пароля'

Then пользователь видит сообщение об ошибке 'Значение слишком короткое. Должно быть равно 6 символам или больше.'