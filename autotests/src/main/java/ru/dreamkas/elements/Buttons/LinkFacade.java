package ru.dreamkas.elements.Buttons;

import org.openqa.selenium.By;
import ru.dreamkas.common.pageObjects.CommonPageObject;
import ru.dreamkas.elements.Buttons.abstraction.AbstractFacade;
import ru.dreamkas.elements.Buttons.interfaces.Disableable;

public class LinkFacade extends AbstractFacade implements Disableable {

    private static final String XPATH_PATTERN = "//a[normalize-space(text())='%s']";

    public LinkFacade(CommonPageObject pageObject, String linkText) {
        super(pageObject, linkText);
    }

    public LinkFacade(CommonPageObject pageObject, By customFindBy) {
        super(pageObject, customFindBy);
    }

    @Override
    public String getXpathPattern() {
        return XPATH_PATTERN;
    }

    @Override
    public Boolean isDisabled() {
        return null != getPageObject().findElement(getFindBy()).getAttribute("disabled");
    }
}
