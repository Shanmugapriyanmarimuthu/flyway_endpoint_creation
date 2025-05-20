
<?php
$url = "https://copilot-app.condenast.io/vin/articles/682b7b059c1cd8ea2635b002";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.5',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1',
    'Cache-Control: max-age=0'
]);

curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36');

curl_setopt($ch, CURLOPT_REFERER, 'https://google.com');
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} else {
    echo htmlentities($response);
}

curl_close($ch);
?>


<?php
exit;
$url = "https://copilot-app.condenast.io/vin/articles/682b7b059c1cd8ea2635b002";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Optional: to follow redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$html = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch);
} else {
    echo htmlentities($html);
}

curl_close($ch);
exit;
?>







<?php
exit;
$url = "https://copilot-app.condenast.io/vin/articles/682b7b059c1cd8ea2635b002";

$html = file_get_contents($url);

if ($html !== false) {
    echo htmlentities($html); // or save/parse as needed
} else {
    echo "Failed to fetch URL.";
}
?>

<?php

exit;
// Include Parsedown library
require 'vendor/autoload.php';

$Parsedown = new Parsedown();

// Sample Markdown content
$markdownContent = "From sorbet to dandelion, canary to electric, the colour yellow has been worn time and again, in all shades, by Indian brides. So it comes as no surprise that the cheery hue won\r\n [Shloka Mehta ](http:\/\/www.vogue.in\/content\/akash-ambani-shloka-mehta-post-wedding-celebration-inside-pictures-maroon5-honey-singh-performance-video\/) \r\n 's vote recently as well. In case you missed it, the newest member of the\r\n [Ambani ](http:\/\/www.vogue.in\/content\/met-gala-2019-isha-ambani-in-designer-prabal-gurung-lavender-gown-red-carpet\/) \r\n family favoured the hue back in March 2018, when she made her first public outing post-engagement with\r\n [Akash Ambani ](http:\/\/www.vogue.in\/content\/akash-ambani-shloka-mehta-wedding-celebration-guest-pictures-bollywood-celebrities\/) \r\n during a visit to Mumbai's Siddhivinayak temple, and worked the colour once again when she picked a a yellow and fuchsia lehenga for Isha Ambani’s mehandi in December 2018. Now, a brand new, never-seen-before snapshot of Mehta shows her in yet another yellow number: a beautifully embroidered lehenga by designer\r\n [Anamika Khanna](http:\/\/www.vogue.in\/content\/designer-anamika-khanna-new-ethnic-wear-collection-ak-ok-lehengas\/), which would make the ideal pick for a bride's haldi ceremony. Though there's no confirmation as to which occasion this image is from, one can safely assumer it may be from Shloka Mehta's own haldi. https:\/\/www.instagram.com\/p\/By2c4RLHC8j\/\r\n \r\n ## Shloka Mehta rounds off her yellow lehenga with a floral maang tikka\r\n\r\n\r\n Brushed with stunning watercolour style strokes of orange on the skirt, Shloka Mehta’s ombre yellow lehenga made a perfect canvas to spotlight its intricate\r\n [embroidery](http:\/\/www.vogue.in\/story\/this-label-will-make-you-fall-in-love-with-embroidery-all-over-again\/). The delicate floral threadwork added a romantic touch to her look, and featured a spectrum of colours ranging from sky blue to soft pink and deep green. While Mehta's blouse was covered in blooms, her skirt featured slightly less heavy work to balance the entire look out. The Anamika Khanna outfit came with the designer’s signature cape dupatta, which is a practical choice for a celebration like a haldi. In the accessories department, Mehta kept her look hassle-free look by ditching heavy earrings and a neckpiece, and choosing a floral\r\n [maang tikka ](http:\/\/www.vogue.in\/content\/bollywood-celebrities-maang-tikka-alia-bhatt-sonam-kapoor\/) \r\n instead. Tying her locks back in a soft bun, she rounded off her makeup with smoky eyes and nude lips. If you haven’t got a yellow lehenga in your bridal trousseau, it’s time to make an investment for your pre-wedding functions. A versatile lehenga like Shloka Mehta’s is also apt for your destination wedding ceremony. Here’s how you can recreate her look.\r\n \r\n [#image: \/photos\/1074656-001] \r\n \r\n \r\n \r\n **Maang Tikka, Ritika Sachdeva**\r\n \r\n \r\n [#image: \/photos\/1074656-002] \r\n \r\n \r\n \r\n \r\n \r\n \r\n **Maang Tikka, Fooljhadi**\r\n \r\n \r\n [#image: \/photos\/1074656-003] \r\n \r\n \r\n \r\n \r\n \r\n \r\n **Lehenga, Anushree Reddy**\r\n \r\n \r\n [#image: \/photos\/1074656-004] \r\n \r\n \r\n \r\n \r\n \r\n \r\n **Lehenga, Anita Dongre**\r\n \r\n \r\n [#image: \/photos\/1074656-005]";

// Convert Markdown to HTML
$htmlContent = $Parsedown->text($markdownContent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown to HTML</title>
</head>
<body>
    <div>
        <?php echo $htmlContent; ?>
    </div>
</body>
</html>
