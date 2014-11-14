Meta:
@sprint_40
@us_103
@us_104
@us_105

Narrative:
Как владелец,
Я хочу создавать, редактировать и удалять поступления товаров от поставщика в магазинах,
Чтобы остатки себестоимости товаров в учетной системе соответствовали действительности

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/товародвижение/создать_магазин_с_ассортиментом.story,
              precondition/товародвижение/создать_приемку.story,
              precondition/товародвижение/создать_списание.story,
              precondition/товародвижение/создать_оприходование.story,
              precondition/товародвижение/создать_возврат_поставщику.story

Scenario: Операции по дате выходит из фильтра дат на странице товародвижения

Meta:
@smoke
@id_s40u103filterDates1

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| dateTo | 27.07.2014 |
| dateFrom | 01.07.2014 |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет, что на странице присутствует текст 'Не найдено ни одной операции с товарами.'

Scenario: Операции по дате входит из фильтра дат на странице товародвижения

Meta:
@smoke
@id_s40u103filterDates2

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| dateTo | 20.08.2014 |
| dateFrom | 29.07.2014 |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |

Scenario: Поля Фильтра дат равны дате операции на странице товародвижения

Meta:
@smoke
@id_s40u103filterDates3

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| dateTo | 19.08.2014 |
| dateFrom | 19.08.2014 |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |

Scenario: Фильтрация операций по типу Приемка на странице товародвижения

Meta:
@smoke
@id_s40u103typeFilters1

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Сбросить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 26.08.2014  | Оприходование | | В stockMovement-store2 | 104,97 |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| types | Приёмка |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

Scenario: Фильтрация операций по типу Списаний на странице товародвижения

Meta:
@smoke
@id_s40u103typeFilters2

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Сбросить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 26.08.2014  | Оприходование | | В stockMovement-store2 | 104,97 |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| types | Списание |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | store | sumTotal |
| 19.08.2014  | Списание | Из stockMovement-store1 | 23,98 |

Scenario: Сброс фильтров операций по типу на странице товародвижения

Meta:
@smoke
@id_s40u103typeFilters3

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Сбросить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 26.08.2014  | Оприходование | | В stockMovement-store2 | 104,97 |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| types | Списание |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | store | sumTotal |
| 19.08.2014  | Списание | Из stockMovement-store1 | 23,98 |

When пользователь нажимает на кнопку Сбросить фильтры на странице товародвижения

Then пользователь проверяет поля на странице товародвижения
| elementName | value |
| types | Все операции |

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 26.08.2014  | Оприходование | | В stockMovement-store2 | 104,97 |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

Scenario: Фильтрация операций по типу Оприходований на странице товародвижения

Meta:
@smoke
@id_s40u103typeFilters4

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Сбросить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 26.08.2014  | Оприходование | | В stockMovement-store2 | 104,97 |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| types | Оприходование |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | store | sumTotal |
| 26.08.2014  | Оприходование | В stockMovement-store2 | 104,97 |

Scenario: Фильтрация операций по типу Возвратов поставщику на странице товародвижения

Meta:
@smoke
@id_s40u103typeFilters5

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'stockMovement@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Сбросить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 26.08.2014  | Оприходование | | В stockMovement-store2 | 104,97 |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |
| 19.08.2014  | Списание | | Из stockMovement-store1 | 23,98 |
| 28.07.2014  | Приёмка | / не оплачена | В stockMovement-store1 | 750,00 |

When пользователь вводит данные в поля на странице товародвижения
| elementName | value |
| types | Возврат поставщику |
And пользователь нажимает на кнопку Применить фильтры на странице товародвижения

Then пользователь проверяет конкретные операции на странице товародвижения
| date | type | status | store | sumTotal |
| 25.08.2014  | Возврат поставщику | / погашен | Из stockMovement-store1 | 991,30 |