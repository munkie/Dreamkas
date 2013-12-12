package project.lighthouse.autotests.jbehave.departmentManager.reports;

import net.thucydides.core.annotations.Steps;
import org.jbehave.core.annotations.Then;
import project.lighthouse.autotests.steps.departmentManager.reports.StoreGrossSaleByHourReportSteps;

public class ThenStoreGrossSaleByHourReportUserSteps {

    @Steps
    StoreGrossSaleByHourReportSteps storeGrossSaleByHourReportSteps;

    @Then("the user checks the store gross sale by hour report contains correct data")
    public void thenTheUserChecksTheStoreGrossSaleByHourReportContainsCorrectData() {
        storeGrossSaleByHourReportSteps.compareWithExampleTable();
    }

    @Then("the user checks the store gross sale by hour report dont contain data on current hour")
    public void thenTheUserChecksTheStoreGrossSaleByHourReportDontContainsDataOnCurrentHour() {
        storeGrossSaleByHourReportSteps.notContainsCurrentHour();
    }
}