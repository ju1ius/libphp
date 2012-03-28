#! /usr/bin/env php
<?php
require_once __DIR__.'/../../../../cssparser/lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace(
  'ju1ius',
  array(
    __DIR__.'/../../../lib',
  )
);
$loader->registerNamespace('Zend', __DIR__.'/../../../../html-email-preflight/lib/vendor');
$loader->register();

$fmt = new ju1ius\Html\Formatter\PlainText();
$fmt->loadHtmlFile('/home/ju1ius/src/php/html-email-preflight/test/xsl/premailer.html');
//$fmt->loadHtmlFile('http://www.jean-luc-melenchon.fr/2012/02/18/sarkozy-veut-un-regime-plebiscitaire/');
//$fmt->loadHtmlFile('https://www.google.com/search?q=php+count+number+of+chars+end+of+string&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:en-US:unofficial&client=iceweasel-a');
echo $fmt->format();
die;
//
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->loadXml(<<<EOS
<txt>
	<txt lines="1">
		<txt lines="nope"></txt>
	</txt>
	<txt lines="0">
		<txt lines="12"></txt>
	  <txt lines="3">
	    <txt lines-after="2"></txt>
	    <txt></txt>
	  </txt>
		<txt lines-before="2"></txt>
	</txt>
</txt>
EOS
);
$dom->normalize();
$xpath = new \DOMXPath($dom);
$root = $xpath->query('/txt')->item(0);

//$r = $xpath->evaluate(
	//'.//txt[last() and ancestor::txt[last()]]',
	//$root
//);
//$it = $root;
//$lines = 0;
//while(($it = $it->lastChild )&& $it instanceof DOMElement) {
	//var_dump($it->getNodePath());
	//if($it->hasAttribute('lines')) {
		//$line = (int) $it->getAttribute('lines');
		//var_dump($line);
		//if($line > $lines) $lines = $line;
	//}
//}
//var_dump($lines);
//foreach ($r as $node) {
	//var_dump($node->getAttribute('lines'));
//}

