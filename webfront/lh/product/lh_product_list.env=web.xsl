<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template
        match=" *[ @lh_application_view = 'lh_product_list' ] "
        >
        <div lh_card_stack="true">
            <div lh_card="true">
                <div lh_card_header="true">
                    <span lh_card_title="true">
                        Товары
                    </span>
                    <a
                        href="?product/create"
                        lh_button="create"
                        >
                        Новый товар
                    </a>
                </div>
                
                <xsl:apply-templates select=" html | error " mode="lh_error" />
                <xsl:apply-templates select=" products " mode="lh_product_list" />
                
            </div>
        </div>
    </xsl:template>
    
    <xsl:template
        match=" * "
        mode="lh_product_list"
        >
    </xsl:template>
    
    <xsl:template
        match=" *[ product ] "
        mode="lh_product_list"
        >
        <div lh_table="true" name="products">
            
            <div lh_table_row="true">
                <span
                    lh_table_cell="id"
                    lh_table_header="true"
                    >
                    Артикул
                </span>
                <span
                    lh_table_cell="common"
                    lh_table_header="true"
                    >
                    Название
                </span>
                <span
                    lh_table_cell="common"
                    lh_table_header="true"
                    >
                    Производитель
                </span>
                <span
                    lh_table_cell="common"
                    lh_table_header="true"
                    >
                    Страна
                </span>
                <span
                    lh_table_cell="money"
                    lh_table_header="true"
                    >
                    Цена
                </span>
            </div>
            
            <xsl:apply-templates select=" product " mode="lh_product_list_item" />
        </div>
    </xsl:template>

    <xsl:template
        match=" * "
        mode="lh_product_list_item"
        >
        <a
            lh_table_row="true"
            name="product"
            id="product={ id }"
            href="?product={ id }"
            >
            <span lh_table_cell="id" name="sku">
                <xsl:value-of select=" sku " />
            </span>
            <span lh_table_cell="common" name="name">
                <xsl:value-of select=" name " />
            </span>
            <span lh_table_cell="common" name="vendor">
                <xsl:value-of select=" vendor " />
            </span>
            <span lh_table_cell="common" name="vendorCountry">
                <xsl:value-of select=" vendorCountry " />
            </span>
            <span lh_table_cell="money">
                <span name="purchasePrice">
                    <xsl:apply-templates select=" purchasePrice " mode="lh_money_view" />
                </span>
                р.
            </span>
        </a>
    </xsl:template>

</xsl:stylesheet>