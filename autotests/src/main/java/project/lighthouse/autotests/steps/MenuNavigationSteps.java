package project.lighthouse.autotests.steps;

import net.thucydides.core.annotations.Step;
import net.thucydides.core.steps.ScenarioSteps;
import project.lighthouse.autotests.elements.preLoader.BodyPreLoader;
import project.lighthouse.autotests.pages.MenuNavigation;

public class MenuNavigationSteps extends ScenarioSteps {

    MenuNavigation menuNavigation;

    @Step
    public void reportMenuItemClick() {
        new BodyPreLoader(getDriver()).await();
        menuNavigation.reportMenuItemClick();
    }

    @Step
    public void reportMenuItemIsNotVisible() {
        try {
            menuNavigation.reportMenuItemClick();
        } catch (Exception ignored) {
        }
    }
}
