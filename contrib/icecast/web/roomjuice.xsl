<xsl:stylesheet xmlns:xsl = "http://www.w3.org/1999/XSL/Transform" version = "1.0" >
<xsl:output method="html" indent="yes" />
<xsl:template match = "/icestats" >

<xsl:for-each select="source">
	<xsl:choose>
		<xsl:when test="listeners">
		</xsl:when>
		<xsl:otherwise>Stream Not Available</xsl:otherwise>
	</xsl:choose>

	Stream Type: <xsl:value-of select="type" /> - <xsl:value-of select="bitrate" /> kpbs<br />
	Number of listeners: <xsl:value-of select="listeners" />
</xsl:for-each>

</xsl:template>
</xsl:stylesheet>
