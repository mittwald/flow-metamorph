<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:param name="package"/>
    <xsl:param name="date"/>
    <xsl:param name="language" select="'default'"/>

    <xsl:template match="/">
        <xliff version="1.0">
            <file>
                <xsl:attribute name="source-language">en</xsl:attribute>
                <xsl:attribute name="datatype">plaintext</xsl:attribute>
                <xsl:attribute name="original">messages</xsl:attribute>
                <xsl:attribute name="date"><xsl:value-of select="$date"/></xsl:attribute>
                <xsl:attribute name="product-name"><xsl:value-of select="$package"/></xsl:attribute>

                <xsl:if test="$language != 'default'">
                    <xsl:attribute name="target-language"><xsl:value-of select="$language"/></xsl:attribute>
                </xsl:if>

                <header/>

                <body>
                    <xsl:apply-templates select="/T3locallang/data/languageKey[@index='default']/label"/>
                </body>
            </file>
        </xliff>
    </xsl:template>

    <xsl:template match="label">
        <xsl:variable name="id" select="@index"/>
        <trans-unit>
            <xsl:attribute name="id"><xsl:value-of select="$id" /></xsl:attribute>
            <source><xsl:value-of select="text()" /></source>
            <xsl:if test="$language != 'default'">
                <target>
                    <xsl:value-of select="/T3locallang/data/languageKey[@index=$language]/label[@index=$id]/text()"/>
                </target>
            </xsl:if>
        </trans-unit>
    </xsl:template>
</xsl:stylesheet>