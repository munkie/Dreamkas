<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/purchases/purchase">
<xsl:for-each select="positions/position/@goodsCode"><xsl:value-of select="."/>,
</xsl:for-each>
</xsl:template>

</xsl:stylesheet>