package ru.dreamkas.pages.pos;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import ru.dreamkas.elements.bootstrap.buttons.PrimaryBtnFacade;
import ru.dreamkas.elements.items.Input;
import ru.dreamkas.elements.items.NonType;
import ru.dreamkas.pages.modal.ModalWindowPage;

public class ReceiptPositionEditModalWindow extends ModalWindowPage {

    public ReceiptPositionEditModalWindow(WebDriver driver) {
        super(driver);
    }

    @Override
    public void createElements() {
        put("sellingPrice", new Input(this, "//*[@name='price']"));
        put("quantity", new Input(this, "//*[@name='quantity']"));
        put("itemPrice", new NonType(this, "//*[@name='itemPrice']"));
        put("name", new NonType(this, "//*[@name='name']"));
        put("barcode", new NonType(this, "//*[@name='barcode']"));
        put("plusButton", new NonType(this, "//*[contains(@class, 'inputNumber__countUp')]"));
        put("minusButton", new NonType(this, "//*[contains(@class, 'inputNumber__countDown')]"));
        putDefaultConfirmationOkButton(
                new PrimaryBtnFacade(this, "Сохранить"));
    }

    @Override
    public String modalWindowXpath() {
        return "//*[@id='modal_receiptProduct']";
    }

    @Override
    public String getTitle() {
        return findVisibleElement(By.xpath(modalWindowXpath() + "//*[@class='modal__title']")).getText();
    }
}
