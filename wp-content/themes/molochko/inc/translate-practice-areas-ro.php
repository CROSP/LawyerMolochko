<?php
/**
 * One-time: set Romanian section heading on RO front page and translate the 6 practice area
 * posts (title, excerpt, content). Run after you have created RO copies of the UA practice areas.
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/translate-practice-areas-ro.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/translate-practice-areas-ro.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_current_language' ) ) {
	echo "Polylang is required.\n";
	exit( 1 );
}

// UA title => RO (title, excerpt, content)
$ro_practice_areas = array(
	'Військовий адвокат' => array(
		'title'   => 'Avocat militar',
		'excerpt' => 'Consultanțe privind mobilizarea, amânarea, VLC, TCC, SZC. Protecția drepturilor rezerviștilor și a familiilor lor.',
		'content' => '<p>În condiții de lege marțială, fiecare rezervist sau mobilizat poate avea nevoie de asistență juridică calificată. Biroul de avocatură Taras Molochko oferă asistență în probleme de mobilizare, amânare, contestarea deciziilor VLC și TCC, obținerea statutului de UBD, plăți și eliberare din serviciul militar.</p>
<p>Avocatul militar este un specialist care înțelege specificul serviciului militar, mobilizării, demobilizării și garanțiilor sociale. Îi însoțim pe clienți în cauze privind contestarea deciziilor VLC, amânarea de la încorporare, amenzi TCC, părăsirea fără voie a unității (SZC), plăți și alte aspecte ale dreptului militar.</p>
<p>Oferim consultanțe în persoană și online, pregătirea documentelor, reprezentarea intereselor la comisariate și în instanță. Contactați-ne pentru o consultație — vom evalua perspectivele cauzei și vom propune varianta optimă de acțiune.</p>',
	),
	'Сімейне право' => array(
		'title'   => 'Dreptul familiei',
		'excerpt' => 'Divorț, împărțirea averii, pensie alimentară, stabilirea locuinței minorului și a programului de legături.',
		'content' => '<p>Disputele familiale necesită acompaniament juridic competent: fiecare situație este individuală, iar consecințele deciziilor afectează viitorul întregii familii. Biroul de avocatură Taras Molochko oferă asistență în cauze de divorț, împărțirea averii comune a soților, încasarea pensiei alimentare, stabilirea locuinței minorului și a programului de legături.</p>
<p>Problemele de drept familial sunt reglementate de Codul Familiei. O consultație la timp cu un avocat ajută la evitarea greșelilor și la protejarea intereselor dumneavoastră. Participăm la negocieri pre-judiciare și reprezentăm clientul în instanță.</p>
<p>Contactați-ne pentru o consultație — vă vom explica drepturile și opțiunile de rezolvare a litigiului.</p>',
	),
	'Спадкові справи' => array(
		'title'   => 'Succesiuni',
		'excerpt' => 'Acceptarea succesorală conform legii și conform testamentului, recunoașterea dreptului asupra bunurilor succesorale, contestația testamentelor.',
		'content' => '<p>Cauzele succesorale sunt adesea însoțite de conflicte între moștenitori și de respectarea termenelor și procedurilor. Biroul de avocatură Taras Molochko oferă asistență la acceptarea moștenirii, recunoașterea dreptului de proprietate asupra bunurilor succesorale, contestarea testamentului și rezolvarea litigiilor între moștenitori.</p>
<p>Vă ajutăm să adunați documentele necesare, să formalizați dreptul la moștenire la notar sau prin instanță, să stabiliți un termen suplimentar pentru acceptarea moștenirii și să vă protejăm interesele la împărțirea averii. O consultație vă va permite să vă clarificați opțiunile și să evitați pierderea drepturilor asupra moștenirii.</p>',
	),
	'Пенсійне право' => array(
		'title'   => 'Dreptul pensiilor',
		'excerpt' => 'Acordarea pensiei, recalculare, contestarea refuzurilor fondului de pensii, asistență în litigiile privind pensiile.',
		'content' => '<p>Refuzurile fondului de pensii de a acorda sau recalcula pensia, întârzierile la plată sau cuantumul incorect sunt situații frecvente care necesită protecție juridică. Biroul de avocatură Taras Molochko oferă asistență la acordarea pensiei, recalculare ținând cont de toate perioadele de vechime, contestarea deciziilor și inacțiunii organelor Fondului de pensii.</p>
<p>Pregătim cereri, plângeri și acțiuni, reprezentăm interesele clientului la fondul de pensii și în instanță. O solicitare la timp către un avocat ajută la apărarea dreptului la pensie și la obținerea plăților cuvenite.</p>',
	),
	'Кримінальне право' => array(
		'title'   => 'Drept penal',
		'excerpt' => 'Apărare în faza de urmărire penală și în instanță, contestarea sentințelor, apărarea victimelor.',
		'content' => '<p>O cauză penală necesită apărare calificată din primele zile: de la reținere și interogatoriu până la judecată. Biroul de avocatură Taras Molochko oferă apărare suspecților și acuzaților în toate etapele procedurii penale, precum și reprezentarea intereselor victimelor.</p>
<p>Consultația cu un avocat într-o cauză penală este individuală și depinde de stadiul cauzei, tipul infracțiunii și obiectivele dumneavoastră. Analizăm materialele, construim tactica de apărare, pregătim cereri și plângeri, reprezentăm clientul în timpul acțiunilor de urmărire și în instanță. Implicarea la timp a unui avocat este adesea decisivă pentru rezultatul cauzei.</p>',
	),
	'Адміністративне право' => array(
		'title'   => 'Drept administrativ',
		'excerpt' => 'Contestarea actelor și deciziilor autorităților, proceduri administrative, accidente rutiere, retragerea permisului.',
		'content' => '<p>Actele ilegale sau inacțiunea organelor de stat, amenzi, retragerea permisului de conducere sau alte sancțiuni administrative pot fi contestate. Biroul de avocatură Taras Molochko reprezentă interesele clienților în cauze de contravenții administrative, inclusiv legate de accidente rutiere, și la contestarea deciziilor autorităților.</p>
<p>Pregătim plângeri către organele superioare și acțiuni administrative în instanță, reprezentăm clientul în timpul dezbaterii. O consultație vă va ajuta să evaluați șansele de succes și să alegeți strategia optimă.</p>',
	),
);

// 1) RO front page: set section heading ACF
$default_fid = (int) get_option( 'page_on_front' );
$ro_fid = $default_fid && function_exists( 'pll_get_post' ) ? (int) pll_get_post( $default_fid, 'ro' ) : 0;

if ( $ro_fid && function_exists( 'update_field' ) ) {
	update_field( 'practice_areas_subtitle', 'Expertiza noastră', $ro_fid );
	update_field( 'practice_areas_title', 'Domenii de practică juridică', $ro_fid );
	update_field( 'practice_areas_description', 'Oferim asistență juridică calificată în materie penală, litigii din accidente rutiere, probleme militare, dispute familiale și de muncă. Avem experiență semnificativă în aceste domenii și garantăm o abordare individuală pentru fiecare caz.', $ro_fid );
	echo "Updated RO front page (ID: {$ro_fid}) ACF: practice_areas_subtitle, practice_areas_title, practice_areas_description.\n";
} else {
	echo "RO front page not found or ACF unavailable. Skipping section heading.\n";
}

// 2) For each UA practice area, find RO translation and set title, excerpt, content
// Get default-language (UA) posts so we find their RO translations
$pll_default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
$ua_posts = get_posts( array(
	'post_type'      => 'pxl-practice-area',
	'posts_per_page' => -1,
	'post_status'    => 'any',
	'lang'           => $pll_default ? $pll_default : '',
) );

$updated = 0;
foreach ( $ua_posts as $post ) {
	$ua_title = $post->post_title;
	if ( ! isset( $ro_practice_areas[ $ua_title ] ) ) {
		continue;
	}
	$ro_post_id = (int) pll_get_post( $post->ID, 'ro' );
	if ( ! $ro_post_id ) {
		echo "No RO translation for: {$ua_title} (ID: {$post->ID}). Create RO copy in Polylang.\n";
		continue;
	}
	$ro = $ro_practice_areas[ $ua_title ];
	$result = wp_update_post( array(
		'ID'           => $ro_post_id,
		'post_title'   => $ro['title'],
		'post_excerpt' => $ro['excerpt'],
		'post_content' => $ro['content'],
	), true );
	if ( ! is_wp_error( $result ) ) {
		$updated++;
		echo "Translated RO: {$ro['title']} (ID: {$ro_post_id}).\n";
	}
}

echo "Done. Translated {$updated} practice area post(s) to Romanian.\n";
