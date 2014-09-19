package project.lighthouse.autotests.collection.abstractObjects.objectInterfaces;

import project.lighthouse.autotests.collection.compare.CompareResults;

import java.util.Map;

/**
 * Interface for {@link project.lighthouse.autotests.collection.abstractObjects.AbstractObject} to get compare result error
 */
public interface ResultComparable {

    public CompareResults getCompareResults(Map<String, String> row);
}
