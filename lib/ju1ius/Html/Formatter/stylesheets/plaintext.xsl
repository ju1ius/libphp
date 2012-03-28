<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:txt="http://github.com/ju1ius/html-email-preflight">
  
<xsl:output method="xml"
    omit-xml-declaration="no"
    encoding="utf-8"
    indent="yes" />

<xsl:strip-space elements="*" />

<!-- Ninja HTML Technique (http://chaoticpattern.com/article/manipulating-html-in-xml/) -->

<xsl:template match="*" mode="html">
	<xsl:element name="{name()}">
		<xsl:apply-templates select="* | @* | text()"/>
	</xsl:element>
</xsl:template>

<xsl:template match="@*" mode="html">
	<xsl:attribute name="{name(.)}">
		<xsl:value-of select="."/>
	</xsl:attribute>
</xsl:template>

<!-- here we go -->

<xsl:template match="/">
  <txt:document>
	  <xsl:apply-templates select="*" />
  </txt:document>
</xsl:template>

<!-- we shurely don't want to see those -->

<xsl:template match="head | style | script | noscript |
  iframe | object | embed | applet | map | area | form | canvas" />

<!-- Unhandled tags are replaced by their node value -->

<!--<xsl:template match="*[not(-->
  <!--self::a          | self::img              | self::hr     | self::br     | -->
  <!--self::em         | self::i                | self::strong | self::b      | -->
  <!--self::ul         | self::ol               | self::li     | -->
  <!--self::blockquote | self::p                | self::pre    | self::code   | -->
  <!--self::h1         | self::h2               | self::h3     | self::h4     |-->
  <!--self::h5         | self::h6               | -->
  <!--self::table      | self::tr               | self::td     | self::style  | -->
<!--)]">-->
  <!--<xsl:value-of select="text()" />-->
<!--</xsl:template>-->


<!-- line-breaks -->

<xsl:template match="br">
	<xsl:text>&#x20;&#x20;</xsl:text>
	<xsl:text>&#xA;</xsl:text>
</xsl:template>

<!-- links -->

<xsl:template match="a">
  <xsl:text>&#x20;</xsl:text>
  <xsl:text>[</xsl:text>
  <xsl:apply-templates select="* | text()"/>
  <xsl:text>](</xsl:text>
  <xsl:value-of select="@href"/>
  <xsl:if test="@title != ''">
    <xsl:text>&#x20;"</xsl:text>
    <xsl:value-of select="@title"/>
    <xsl:text>"</xsl:text>
  </xsl:if>
  <xsl:text>)</xsl:text>
  <xsl:text>&#x20;</xsl:text>
</xsl:template>

<xsl:template match="img[@alt and not(contains(@class, 'separator'))]">
  <xsl:text>&#x20;</xsl:text>
  <xsl:value-of select="@alt" />
  <xsl:text>&#x20;</xsl:text>
</xsl:template>

<xsl:template match="hr|img[contains(@class, 'separator')]">
  <txt:separator char="-" />
</xsl:template>


<!-- em, strong -->

<xsl:template match="em|i">
  <xsl:text>_</xsl:text>
  <xsl:apply-templates select="* | text()"/>
  <xsl:text>_</xsl:text>
</xsl:template>

<xsl:template match="strong|b" >
  <xsl:text>__</xsl:text>
  <xsl:apply-templates select="* | text()"/>
  <xsl:text>__</xsl:text>
</xsl:template>

<!-- p, br, hr -->

<xsl:template match="p">
  <txt:block lines-after="1">
	  <xsl:apply-templates select="* | text()"/>
  </txt:block>
</xsl:template>

<!-- pre -->

<xsl:template match="pre">
  <txt:block raw="true" lines-after="1">
    <!--<xsl:value-of select="text()" />-->
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<!-- h1, h2 -->

<xsl:template match="h1" >
  <txt:block lines-after="1" lines-before="2" border-bottom="=" border-top="=">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<xsl:template match="h2" >
  <txt:block lines-after="1" lines-before="2" border-bottom="=">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<!-- h3, h4, h5, h6 -->

<xsl:template match="h3" >
  <txt:block lines-after="1" lines-before="2" border-bottom="-">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<xsl:template match="h4" >
  <txt:block lines-after="1" lines-before="2" bullet="### ">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<xsl:template match="h5" >
  <txt:block lines-after="1" lines-before="2" bullet="######### ">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<xsl:template match="h6" >
  <txt:block lines-after="1" lines-before="2" bullet="############ ">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<!-- lists -->

<xsl:template match="ul[not(parent::li)]|ol[not(parent::li)]">
  <txt:block lines-after="1">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<xsl:template match="ul/li">
  <txt:block indent="  " bullet="* ">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<xsl:template match="ol/li">
  <txt:block indent="  ">
    <xsl:attribute name="bullet">
      <!-- numeric bullet -->
      <xsl:for-each select="ancestor-or-self::li[not(parent::ul)]">
        <xsl:value-of select="count(preceding-sibling::li)+1"/>
        <xsl:text>.</xsl:text>
      </xsl:for-each>
      <xsl:text>&#x20;</xsl:text>
    </xsl:attribute>
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<!-- blockquotes -->

<xsl:template match="blockquote">
  <txt:block indent="&gt; " lines-after="1">
    <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>

<!-- Tables -->

<xsl:template match="table">
  <txt:block lines-after="1">
    <xsl:apply-templates select="*" />
  </txt:block>
</xsl:template>

<xsl:template match="tr">
  <txt:block lines-after="1">
	  <xsl:apply-templates select="*" />
  </txt:block>
</xsl:template>

<xsl:template match="td|th">
  <txt:block lines-after="1">
	  <xsl:apply-templates select="* | text()" />
  </txt:block>
</xsl:template>


<!-- ASCII Tables -->

<xsl:template match="table[@data-toplaintext]">
  <txt:table>
    <xsl:apply-templates select="*" mode="table"/>
  </txt:table>
</xsl:template>

<xsl:template match="tr" mode="table">
  <txt:tr>
    <xsl:apply-templates select="*" mode="table" />
  </txt:tr>
</xsl:template>

<xsl:template match="td|th" mode="table">
  <txt:td>
    <xsl:apply-templates select="* | text()" />
  </txt:td>
</xsl:template>

<!-- whitespace handling | escape number-period-space-sequences-->

<xsl:template match="text()[not(ancestor::pre)]">
	<xsl:choose>
		<xsl:when test="translate(., '&#xA;&#xD;&#x9;&#x20;', '') = ''">
			<xsl:text>&#x20;</xsl:text>
		</xsl:when>
		<xsl:otherwise>
			<xsl:variable name="text-w-spaces" select="translate(., '&#xA;&#xD;&#x9;&#x20;', '&#x20;&#x20;&#x20;&#x20;')"/>
			<xsl:variable name="leading-char">
				<xsl:if test="substring($text-w-spaces, 1, 1) = '&#x20;'">
					<xsl:text>&#x20;</xsl:text>
				</xsl:if>
			</xsl:variable>
			<xsl:variable name="trailing-char">
				<xsl:if test="substring($text-w-spaces, string-length($text-w-spaces), 1) = '&#x20;'">
					<xsl:text>&#x20;</xsl:text>
				</xsl:if>
			</xsl:variable>
			<xsl:variable name="string" select="concat($leading-char, normalize-space($text-w-spaces), $trailing-char)"/>
      <xsl:value-of select="$string" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
<!--
<xsl:template match="text()[
  position() = 1 or
  ((preceding-sibling::*)[last()][self::address or
                                  self::blockquote or
                                  self::div        or
                                  self::dl         or
                                  self::fieldset   or
                                  self::form       or
                                  self::h1         or
                                  self::h2         or
                                  self::h3         or
                                  self::h4         or
                                  self::h5         or
                                  self::h6         or
                                  self::hr         or
                                  self::noscript   or
                                  self::ol         or
                                  self::p          or
                                  self::pre        or
                                  self::table      or
                                  self::ul         or
                                  self::br         ]
  )]">
	<xsl:variable name="text-w-spaces" select="translate(., '&#xA;&#xD;&#x9;&#x20;', '&#x20;&#x20;&#x20;&#x20;')"/>
	<xsl:choose>
		<xsl:when test="translate(., '&#xA;&#xD;&#x9;&#x20;', '') = ''">
			<xsl:text></xsl:text>
		</xsl:when>
		<xsl:otherwise>
			<xsl:variable name="trailing-char">
				<xsl:if test="substring($text-w-spaces, string-length($text-w-spaces), 1) = '&#x20;'">
					<xsl:text>&#x20;</xsl:text>
				</xsl:if>
			</xsl:variable>
			<xsl:variable name="string" select="concat(normalize-space($text-w-spaces), $trailing-char)"/>
      <xsl:value-of select="$string" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
-->

</xsl:stylesheet>
