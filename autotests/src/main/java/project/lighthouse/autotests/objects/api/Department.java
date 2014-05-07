package project.lighthouse.autotests.objects.api;

import org.json.JSONException;
import org.json.JSONObject;
import project.lighthouse.autotests.objects.api.abstraction.AbstractObject;

public class Department extends AbstractObject {
    final public static String NAME = "department";
    final public static String API_URL = "/departments";

    final public static String DEFAULT_NUMBER = "departmentDefaultNumber";
    final public static String DEFAULT_NAME = "department default name";

    public Department(JSONObject jsonObject) {
        super(jsonObject);
    }

    public Department(String number, String name) throws JSONException {
        this(new JSONObject()
                .put("number", number)
                .put("name", name)
        );
    }

    public Department(String number, String name, String storeId) throws JSONException {
        this(new JSONObject()
                .put("number", number)
                .put("name", name)
                .put("store", storeId)
        );
    }

    public Department() throws JSONException {
        this(DEFAULT_NUMBER, DEFAULT_NAME);
    }

    @Override
    public String getApiUrl() {
        return API_URL;
    }

    public String getNumber() throws JSONException {
        return getPropertyAsString("number");
    }

    public String getStoreID() throws JSONException {
        return jsonObject.getJSONObject("store").getString("id");
    }
}