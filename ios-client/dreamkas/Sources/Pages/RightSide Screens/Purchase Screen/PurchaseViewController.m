//
//  PurchaseViewController.m
//  dreamkas
//
//  Created by sig on 13.11.14.
//  Copyright (c) 2014 Dreamkas. All rights reserved.
//

#import "PurchaseViewController.h"
#import "MoreButton.h"
#import "PurchaseItemCell.h"

@interface PurchaseViewController ()

@end

@implementation PurchaseViewController

#pragma mark - Инициализация

- (void)initialize
{
    // выключаем для контроллера массовое обновление и лимитированные запросы
    [self setPullDownActionEnabled:NO];
    [self setLimitedQueryEnabled:NO];
}

#pragma mark - View Lifecycle

- (void)viewDidLoad
{
    [super viewDidLoad];
    
    self.title = @"Чек";
    
    [self placeMoreBarButton];
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    
    // ..
}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewDidAppear:animated];
    
    // ..
}

#pragma mark - Configuration Methods

- (void)placeMoreBarButton
{
    MoreButton *btn = [MoreButton buttonWithType:UIButtonTypeCustom];
    btn.frame = CGRectMake(0, 0, DefaultTopPanelHeight, DefaultTopPanelHeight);
    [btn setAccessibilityLabel:AI_TicketWindowPage_SearchButton];
    [btn addTarget:self action:@selector(moreButtonClicked) forControlEvents:UIControlEventTouchUpInside];
    UIBarButtonItem *right_btn = [[UIBarButtonItem alloc] initWithCustomView:btn];
    self.navigationItem.rightBarButtonItem = right_btn;
}

- (void)configureLocalization
{
    // ..
}

- (void)configureAccessibilityLabels
{
    // ..
}

#pragma mark - Обработка пользовательского взаимодействия

- (void)moreButtonClicked
{
    DPLogFast(@"");
}

#pragma mark - Методы CustomTableViewController

/**
 *  Kласс ячейки таблицы
 */
- (Class)cellClass
{
    return [PurchaseItemCell class];
}

/**
 *  Метод возвращает название класса, чьи экземпляры выбираются из БД и выводятся в таблице
 */
- (Class)fetchClass
{
    return [PurchaseItemModel class];
}

/**
 * Метод возвращает название параметра, по которому происходит сортировка при выборке из БД
 */
- (NSString*)fetchSortedField
{
    return @"pk";
}

/**
 *  Метод показывает направление сортировки при выборке
 *  (YES - по возрастанию, NO - по убыванию)
 */
- (BOOL)isFetchAscending
{
    return YES;
}

/**
 *  Метод возвращает предикат, по которому происходит фильтрация при выборке из БД
 */
- (NSPredicate*)fetchPredicate
{
//    NSMutableArray *argument_array = [NSMutableArray new];
//    NSMutableArray *format_array = [NSMutableArray new];
//    NSPredicate *predicate = nil;
//    
//    [format_array addObject:@"isActive = %@"];
//    [argument_array addObject:@YES];
//    
//    [format_array addObject:@"items.count > %@"];
//    [argument_array addObject:@0];
//    
//    // формируем предикат по полученным данным
//    predicate = [NSPredicate predicateWithFormat:[format_array componentsJoinedByString:@" AND "]
//                                   argumentArray:argument_array];
    return nil;
}

/**
 *  Метод, инициирующий загрузку данных с сервера
 */
- (void)requestDataFromServer
{
//    [super requestDataFromServer];
//    
//    __weak typeof(self)weak_self = self;
//    [NetworkManager requestGroups:^(NSArray *data, NSError *error) {
//        __strong typeof(self)strong_self = weak_self;
//        
//        if (error != nil) {
//            [strong_self onMappingFailure:error];
//            return;
//        }
//        [strong_self onMappingCompletion:data];
//    }];
}

/**
 *  Установка высоты ячейкам таблицы
 */
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    NSString *cell_identifier = [NSString stringWithFormat:@"Cell_%@", [self fetchClass]];
    return [PurchaseItemCell cellHeight:tableView
                         cellIdentifier:cell_identifier
                                  model:[self.fetchedResultsController objectAtIndexPath:indexPath]];
}

@end