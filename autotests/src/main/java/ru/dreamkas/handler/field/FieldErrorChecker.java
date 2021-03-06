package ru.dreamkas.handler.field;

import org.hamcrest.Matchers;
import org.junit.Assert;
import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebElement;
import ru.dreamkas.common.item.CommonItem;
import ru.dreamkas.elements.items.SelectByVisibleText;

public class FieldErrorChecker {

    private CommonItem commonItem;

    public FieldErrorChecker(CommonItem commonItem) {
        this.commonItem = commonItem;
    }

    public CommonItem getCommonItem() {
        return commonItem;
    }

    public void assertFieldErrorMessage(String expectedFieldErrorMessage) {
        try {
            String actualFieldErrorMessage;
            WebElement fieldWebElement = commonItem.getVisibleWebElement();
            // TODO move to page object class
            if (commonItem instanceof SelectByVisibleText) {
                actualFieldErrorMessage = fieldWebElement.findElement(By.xpath("./../../../*[contains(@class, 'form__errorMessage')]")).getText();
            } else {
                actualFieldErrorMessage = fieldWebElement.findElement(By.xpath("./../*[contains(@class, 'form__errorMessage_visible')]")).getText();
            }
            Assert.assertThat(actualFieldErrorMessage, Matchers.is(expectedFieldErrorMessage));
        } catch (NoSuchElementException e) {
            Assert.fail("Field do not have error validation messages");
        }
    }
}
