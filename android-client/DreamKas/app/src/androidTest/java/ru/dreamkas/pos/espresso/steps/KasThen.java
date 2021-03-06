package ru.dreamkas.pos.espresso.steps;


import android.util.Pair;
import android.widget.LinearLayout;

import java.util.ArrayList;

import ru.dreamkas.pos.R;
import ru.dreamkas.pos.controller.Command;
import ru.dreamkas.pos.model.api.Product;

import static com.google.android.apps.common.testing.ui.espresso.Espresso.onData;
import static com.google.android.apps.common.testing.ui.espresso.Espresso.onView;
import static com.google.android.apps.common.testing.ui.espresso.action.ViewActions.click;
import static com.google.android.apps.common.testing.ui.espresso.action.ViewActions.typeText;
import static com.google.android.apps.common.testing.ui.espresso.assertion.ViewAssertions.matches;
import static com.google.android.apps.common.testing.ui.espresso.matcher.ViewMatchers.isDisplayed;
import static com.google.android.apps.common.testing.ui.espresso.matcher.ViewMatchers.withId;
import static com.google.android.apps.common.testing.ui.espresso.matcher.ViewMatchers.withText;
import static org.hamcrest.core.IsNot.not;
import static ru.dreamkas.pos.espresso.EspressoHelper.has;
import static ru.dreamkas.pos.espresso.EspressoHelper.waitForView;
import static ru.dreamkas.pos.espresso.EspressoHelper.withProduct;
import static ru.dreamkas.pos.espresso.EspressoHelper.withReceiptItem;

public class KasThen {
    static Command checkSearchProductResultCommand = new Command<Pair<Integer, ArrayList<Product>>>() {
        @Override
        public void execute(Pair<Integer, ArrayList<Product>> data){
            Integer expectedCount = data.first;
            ArrayList<Product> ethalonContent = data.second;
            onView(withId(R.id.lvProductsSearchResult)).check(has(expectedCount, LinearLayout.class));
            for (Product item : ethalonContent){
                onData(withProduct(item.getName())).inAdapterView(withId(R.id.lvProductsSearchResult)).check(matches(isDisplayed()));
            }
        }};

    static Command checkReceiptCommand = new Command<Pair<Integer, ArrayList<Product>>>() {
        @Override
        public void execute(Pair<Integer, ArrayList<Product>> data){
            Integer expectedCount = data.first;
            ArrayList<Product> ethalonContent = data.second;
            onView(withId(R.id.lvReceipt)).check(has(expectedCount, LinearLayout.class));

            for (Product item : ethalonContent){
                onData(withReceiptItem(item.getName())).inAdapterView(withId(R.id.lvReceipt)).check(matches(isDisplayed()));
            }
        }};

    static Command checkEditReceiptItemModalCommand = new Command<ArrayList<String>>() {
        @Override
        public void execute(ArrayList<String> args){
            String title = args.get(0);
            String productName = args.get(1);
            String sellingPrice = args.get(2);
            String quantity = args.get(3);

            onView(withId(R.id.lblTotal)).check(matches(withText(title)));
            onView(withId(R.id.lblProductName)).check(matches(withText(productName)));
            onView(withId(R.id.txtSellingPrice)).check(matches(withText(sellingPrice)));
            onView(withId(R.id.txtValue)).check(matches(withText(quantity)));
        }
    };



    public static void search(String searchFor) throws InterruptedException {
        onView(withId(R.id.txtProductSearchQuery)).perform(typeText(searchFor));
        waitForView(R.id.pbSearchProduct, 20000, not(isDisplayed()));
    }

    public static void checkSearchProductResult(int expectedCount, ArrayList<Product> content) throws Throwable {
        CommonSteps.tryInTime(checkSearchProductResultCommand, new Pair<Integer, ArrayList<Product>>(expectedCount, content));
    }


    public static void checkEmptyReceipt() {
        waitForView("Для продажи добавьте в чек хотя бы один продукт.", 2000);
        waitForView(R.id.btnRegisterReceipt, 1000, not(isDisplayed()));
        waitForView(R.id.lvReceipt, 1000, not(isDisplayed()));
    }

    public static void checkReceipt(int expectedCount, ArrayList<Product> content) throws Throwable {
        CommonSteps.tryInTime(checkReceiptCommand, new Pair<Integer, ArrayList<Product>>(expectedCount, content));
    }

    public static void checkReceiptTotal(String total) {
        onView(withId(R.id.btnRegisterReceipt)).check(matches(withText(total)));
    }

    public static void checkEditReceiptItemModal(final String title, final String productName, final String sellingPrice, final String quantity) throws Throwable {
        ArrayList<String> args = new ArrayList<String>() {
            {
                add(title);
                add(productName);
                add(sellingPrice);
                add(quantity);
            }
        };
        CommonSteps.tryInTime(checkEditReceiptItemModalCommand, args);
    }
}
