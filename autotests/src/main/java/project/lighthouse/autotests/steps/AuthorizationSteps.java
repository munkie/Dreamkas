package project.lighthouse.autotests.steps;

import net.thucydides.core.annotations.Step;
import net.thucydides.core.steps.ScenarioSteps;
import org.openqa.selenium.Cookie;
import org.openqa.selenium.TimeoutException;
import project.lighthouse.autotests.pages.MenuNavigationBar;
import project.lighthouse.autotests.pages.authorization.AuthorizationPage;
import project.lighthouse.autotests.storage.Storage;

import java.util.HashMap;
import java.util.Map;

import static org.hamcrest.Matchers.equalTo;
import static org.junit.Assert.assertThat;
import static org.junit.Assert.fail;

public class AuthorizationSteps extends ScenarioSteps {

    private Map<String, String> users = new HashMap<String, String>() {{
        put("watchman", "lighthouse");
        put("commercialManager", "lighthouse");
        put("storeManager", "lighthouse");
        put("departmentManager", "lighthouse");
    }};

    AuthorizationPage authorizationPage;
    MenuNavigationBar menuNavigationBar;

    @Step
    public void authorization(String userName) {
        String password = users.get(userName);
        authorization(userName, password);
    }

    @Step
    public void authorization(String userName, String password) {
        authorization(userName, password, false);
    }

    @Step
    public void authorization(String userName, String password, Boolean isFalse) {
        workAroundTypeForUserName(userName);
        authorizationPage.input("password", password);
        authorizationPage.loginButtonClick();
        if (!isFalse) {
            checkUser(userName);
        }
        Storage.getUserVariableStorage().setIsAuthorized(true);
    }

    // TODO fix this in future

    /**
     * This is actually bad type workaround for failing logging in.
     * For some reasons the type method is disturbed and the userName is not typed fully.
     *
     * @param inputText
     */
    @Step
    public void workAroundTypeForUserName(String inputText) {
        authorizationPage.input("userName", inputText);
        if (!authorizationPage.getItems().get("userName").getVisibleWebElementFacade().getValue().equals(inputText)) {
            workAroundTypeForUserName(inputText);
        }
    }

    @Step
    public void beforeScenario() {
        if (Storage.getUserVariableStorage().getIsAuthorized()) {
            Cookie token = getDriver().manage().getCookieNamed("token");
            if (token != null) {
                getDriver().manage().deleteCookie(token);
            }
        }
    }

    @Step
    public void checkUser(String userName) {
        String actualUserName = menuNavigationBar.getUserNameText();
        assertThat(
                String.format("The user name is '%s'. Should be '%s'.", actualUserName, userName),
                actualUserName,
                equalTo(userName));
    }

    @Step
    public void openPage() {
        authorizationPage.open();
    }

    @Step
    public void loginFormIsPresent() {
        try {
            authorizationPage.getLoginFormWebElement();
        } catch (TimeoutException e) {
            fail("The log out is not successful!");
        }
    }

    @Step
    public void authorizationFalse(String userName, String password) {
        authorization(userName, password, true);
        Storage.getUserVariableStorage().setIsAuthorized(false);
    }
}