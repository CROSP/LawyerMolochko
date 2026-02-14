<?php
/**
 * Romanian translations for Polylang registered strings (UA/EN → RO).
 * Used by CLI command polylang-strings-ro.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns map of source string => Romanian translation for Polylang strings.
 *
 * @return array<string, string>
 */
function molochko_polylang_strings_ro_map() {
	return array(
		// Email / site
		'[Lawyer Molochko] Password Reset' => '[Lawyer Molochko] Resetare parolă',
		'[Lawyer Molochko] New User Registration' => '[Lawyer Molochko] Înregistrare utilizator nou',
		'<p>Someone requested to reset the password for the following account:</p>
<p>Username: {{username}}</p>
<p>If this was a mistake, just ignore this email and nothing will happen.</p>
<p>To reset your password, click the button below.</p>
<div style="padding: 10px 0 50px 0; text-align: center;">
    <a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{password_reset_link}}"> Reset Password</a>
</div>' => '<p>Cineva a solicitat resetarea parolei pentru contul:</p>
<p>Utilizator: {{username}}</p>
<p>Dacă nu ați făcut această solicitare, ignorați acest email.</p>
<p>Pentru a reseta parola, faceți clic pe butonul de mai jos.</p>
<div style="padding: 10px 0 50px 0; text-align: center;">
    <a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{password_reset_link}}"> Resetează parola</a>
</div>',
		'<p>New user registration on your site {{site_title}}.</p>
<p>Username: {{username}}</p>
<p>Email address: {{user_email}}</p>' => '<p>Înregistrare utilizator nou pe site-ul {{site_title}}.</p>
<p>Utilizator: {{username}}</p>
<p>Email: {{user_email}}</p>',
		'I have read and agree to the website [terms]' => 'Am citit și sunt de acord cu [termenii] site-ului',
		'Адвокатське бюро &quot;Тараса Молочко&quot;' => 'Biroul de avocatură „Taras Molochko”',
		'F j, Y' => 'j F Y',
		'g:i a' => 'H:i',
		// Reviews CPT
		'Reviews Fields' => 'Câmpuri recenzii',
		'Person name' => 'Nume persoană',
		'Case Category' => 'Categorie caz',
		'Reviews – Related Case Study' => 'Recenzii – Caz de studiu asociat',
		'Related Case Study' => 'Caz de studiu asociat',
		'Optional. Link from this review points to the Case Study archive.' => 'Opțional. Linkul din această recenzie duce la arhiva Cazuri de studiu.',
		// ACF – Contact options
		'Site contact details options page' => 'Pagină opțiuni contacte site',
		'Phone number' => 'Număr telefon',
		'Working hours' => 'Program',
		'Email' => 'Email',
		'Address' => 'Adresă',
		'Instagram' => 'Instagram',
		'Telegram' => 'Telegram',
		'TikTok' => 'TikTok',
		'Instagram Fetch User ID' => 'Instagram Fetch User ID',
		'Instagram Fetch Access Token' => 'Instagram Fetch Access Token',
		// Reviews labels
		'Reviews' => 'Recenzii',
		'Review' => 'Recenzie',
		'All Reviews' => 'Toate recenziile',
		'Edit Review' => 'Editează recenzia',
		'View Review' => 'Vizualizează recenzia',
		'View Reviews' => 'Vizualizează recenziile',
		'Add New Review' => 'Adaugă recenzie nouă',
		'New Review' => 'Recenzie nouă',
		'Parent Review:' => 'Recenzie părinte:',
		'Search Reviews' => 'Caută recenzii',
		'No reviews found' => 'Nu s-au găsit recenzii',
		'No reviews found in Trash' => 'Nu s-au găsit recenzii în Coș',
		'Review Archives' => 'Arhive recenzii',
		'Review Attributes' => 'Atribute recenzie',
		'Insert into review' => 'Inserează în recenzie',
		'Uploaded to this review' => 'Încărcat la această recenzie',
		'Filter reviews list' => 'Filtrează lista de recenzii',
		'Filter reviews by date' => 'Filtrează recenziile după dată',
		'Reviews list navigation' => 'Navigare listă recenzii',
		'Reviews list' => 'Listă recenzii',
		'Review published.' => 'Recenzie publicată.',
		'Review published privately.' => 'Recenzie publicată privat.',
		'Review reverted to draft.' => 'Recenzie readusă la ciornă.',
		'Review scheduled.' => 'Recenzie programată.',
		'Review updated.' => 'Recenzie actualizată.',
		'Review Link' => 'Link recenzie',
		'A link to a review.' => 'Un link către o recenzie.',
		// Case Study Category taxonomy
		'Case Study Categories' => 'Categorii cazuri de studiu',
		'Case Study Category' => 'Categorie caz de studiu',
		'All Case Study Categories' => 'Toate categoriile de cazuri de studiu',
		'Edit Case Study Category' => 'Editează categoria de caz de studiu',
		'View Case Study Category' => 'Vizualizează categoria de caz de studiu',
		'Update Case Study Category' => 'Actualizează categoria de caz de studiu',
		'Add New Case Study Category' => 'Adaugă categorie nouă de caz de studiu',
		'New Case Study Category Name' => 'Nume categorie nouă de caz de studiu',
		'Search Case Study Categories' => 'Caută categorii de cazuri de studiu',
		'Popular Case Study Categories' => 'Categorii populare de cazuri de studiu',
		'Separate case study categories with commas' => 'Separa categoriile de cazuri de studiu cu virgule',
		'Add or remove case study categories' => 'Adaugă sau elimină categorii de cazuri de studiu',
		'Choose from the most used case study categories' => 'Alege din cele mai folosite categorii de cazuri de studiu',
		'No case study categories found' => 'Nu s-au găsit categorii de cazuri de studiu',
		'No case study categories' => 'Fără categorii de cazuri de studiu',
		'Case Study Categories list navigation' => 'Navigare listă categorii cazuri de studiu',
		'Case Study Categories list' => 'Listă categorii cazuri de studiu',
		'← Go to case study categories' => '← Mergi la categorii cazuri de studiu',
		'Case Study Category Link' => 'Link categorie caz de studiu',
		'A link to a case study category' => 'Un link către o categorie de caz de studiu',
		'Categories' => 'Categorii',
		'Tags' => 'Etichete',
		'Newsletter' => 'Newsletter',
		// ACF – Contact options (Ukrainian)
		'Контакти сайту (шапка та загальні)' => 'Contacte site (antet și general)',
		'Шапка сайту' => 'Antet site',
		'Телефон' => 'Telefon',
		'Адреса' => 'Adresă',
		'Посилання на карту (Google Maps)' => 'Link hartă (Google Maps)',
		'Графік роботи' => 'Program',
		'Посилання кнопки «Замовити консультацію»' => 'Link buton «Comandă consultanță»',
		'Основний номер. Відображається в шапці та блоці «Про нас».' => 'Număr principal. Afișat în antet și în blocul «Despre noi».',
		'+38 (050) 606-00-79' => '+38 (050) 606-00-79',
		'Верхня смуга шапки.' => 'Banda superioară a antetului.',
		'Текст адреси у верхній смузі шапки.' => 'Textul adresei în banda superioară a antetului.',
		'Якщо заповнено, адреса стає посиланням на карту.' => 'Dacă este completat, adresa devine link către hartă.',
		'Наприклад: Пн–Пт: 9:00 – 18:00. Перекладається через Polylang.' => 'Ex.: Lun–Vineri: 9:00 – 18:00. Se traduce prin Polylang.',
		'Пн–Пт: 9:00 – 18:00' => 'Lun–Vineri: 9:00 – 18:00',
		'URL або якір, наприклад #contact або /kontakty.' => 'URL sau ancoră, ex. #contact sau /contacte.',
		'#contact' => '#contact',
		'Телефон, email, адреса, графік, кнопка консультації. Зберігаються в ACF Options (Контакти). Переклади — Polylang.' => 'Telefon, email, adresă, program, buton consultanță. Stocate în ACF Options (Contacte). Traduceri — Polylang.',
		'Контакти' => 'Contacte',
		'Замовити консультацію' => 'Comandă consultanță',
		'Адреса офісу' => 'Adresa biroului',
		// URL slugs
		'home' => 'home',
		'Home' => 'Acasă',
		'services' => 'servicii',
		'Services' => 'Servicii',
		'Послуги' => 'Servicii',
		'blog' => 'blog',
		'Blog' => 'Blog',
		'Новини' => 'Știri',
		'reviews' => 'recenzii',
		'Відгуки' => 'Recenzii',
		'cases' => 'cazuri',
		'Cases' => 'Cazuri',
		'Кейси' => 'Cazuri',
		'contact' => 'contact',
		'Contact' => 'Contact',
		'kontakty' => 'contacte',
		// Common
		'Primary Menu' => 'Meniu principal',
		'URL slugs' => 'Slug-uri URL',
		'Widget title' => 'Titlu widget',
		'Widget text' => 'Text widget',
		'ACF' => 'ACF',
		'Title' => 'Titlu',
		'Name' => 'Nume',
		'Label' => 'Etichetă',
		'Description' => 'Descriere',
		'Instructions' => 'Instrucțiuni',
		'Placeholder' => 'Marcă de poziție',
		'Напрямки практики' => 'Domenii de practică',
		'Напрямок практики' => 'Domeniu de practică',
		'Категорії' => 'Categorii',
		// Contact Form 7 – Multilingual CF7 with Polylang (filter "Contact Form 7" in String translations)
		'Ваше ім\'я *' => 'Numele tău *',
		'Телефон *' => 'Telefon *',
		'Тема (необов\'язково)' => 'Subiect (opțional)',
		'Повідомлення *' => 'Mesaj *',
		'Ім\'я' => 'Nume',
		'Номер телефону' => 'Număr de telefon',
		'Коротко про ваше питання' => 'Pe scurt despre problema ta',
		'Опишіть ситуацію або питання' => 'Descrieți situația sau întrebarea',
		'Надіслати заявку' => 'Trimite cererea',
		'Нова заявка на консультацію' => 'Cerere nouă de consultanță',
		'Телефон' => 'Telefon',
		'Тема' => 'Subiect',
		'Повідомлення' => 'Mesaj',
	);
}
