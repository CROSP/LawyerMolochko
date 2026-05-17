<?php
/**
 * One-time: set Romanian section heading on RO front page and translate all practice area
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
	'Земельне право' => array(
		'title'   => 'Dreptul funciar',
		'excerpt' => 'Autorizații de construcție, închiriere și cumpărare-vânzare terenuri, litigii funciare, privatizare.',
		'content' => '<p>Problemele funciare — de la formalizarea drepturilor asupra terenului până la litigii cu vecinii sau cu autoritățile — necesită cunoaștere a legislației funciare și civile. Biroul de avocatură Taras Molochko oferă consultanță și asistență în cauze de închiriere, cumpărare-vânzare terenuri, privatizare, obținere autorizații și soluționare litigii funciare.</p>
<p>Vă ajutăm să adunați documentele, să formalizați contractele, să contestați refuzurile autorităților și să vă apărați dreptul asupra terenului în instanță.</p>',
	),
	'Податкове право' => array(
		'title'   => 'Dreptul fiscal',
		'excerpt' => 'Litigii fiscale, controale ANAF, contestarea notificărilor fiscale, optimizare fiscală.',
		'content' => '<p>Controalele fiscale, impozitele suplimentare, amenzi și penalități creează riscuri serioase pentru afaceri și persoane fizice. Biroul de avocatură Taras Molochko oferă asistență în litigiile fiscale: contestarea deciziilor administrației fiscale, însoțire în timpul controalelor, apărarea intereselor în instanță și consultanță privind legalitatea fiscală.</p>
<p>Asistența juridică la timp permite minimizarea riscurilor și apărarea poziției dumneavoastră în baza legislației în vigoare.</p>',
	),
	'Міграційний адвокат' => array(
		'title'   => 'Avocat migrații',
		'excerpt' => 'Reședință, cetățenie, deportare, documente pentru străini și refugiați.',
		'content' => '<p>Formalizarea reședinței, obținerea cetățeniei, evitarea deportării sau contestarea deciziilor autorităților de migrație necesită cunoaștere a dreptului migrațional și administrativ. Biroul de avocatură Taras Molochko oferă consultanță și asistență străinilor, apatrizilor și ucrainenilor în probleme de relocare, statut și documente.</p>
<p>Vă ajutăm să pregătiți documentele, să depuneți cereri la autoritatea de migrație și reprezentăm interesele clientului la contestarea refuzurilor sau anularea reședinței.</p>',
	),
	'Адвокат з нерухомості' => array(
		'title'   => 'Avocat imobiliar',
		'excerpt' => 'Contracte de cumpărare-vânzare, închiriere, privatizare, litigii imobiliare.',
		'content' => '<p>Contractele imobiliare implică sume mari și riscuri juridice. Biroul de avocatură Taras Molochko oferă asistență la contracte de cumpărare-vânzare și închiriere, ajută la privatizare, verificarea titlului de proprietate și soluționarea litigiilor privind bunurile imobiliare.</p>
<p>Verificăm documentele, pregătim sau analizăm contractele, reprezentăm interesele clientului în negocieri și în instanță. O consultație vă va ajuta să evitați riscurile ascunse și să vă protejați investițiile.</p>',
	),
	'Адвокат з житлових спорів' => array(
		'title'   => 'Avocat litigii locative',
		'excerpt' => 'Evacuare, schimb, recunoașterea dreptului la locuință, litigii cu administratorii.',
		'content' => '<p>Litigiile locative privesc locuința, drepturile asupra apartamentului sau casei, evacuarea, schimbul și serviciile comunale. Biroul de avocatură Taras Molochko oferă asistență la recunoașterea dreptului de proprietate asupra locuinței, contestarea deciziilor de evacuare, repartizarea plăților comunale și alte aspecte locative.</p>
<p>Reprezentăm interesele clienților în instanță și în negocieri cu administratorii și asociațiile de proprietari.</p>',
	),
	'Адвокат по трудових спорах' => array(
		'title'   => 'Avocat litigii de muncă',
		'excerpt' => 'Concediere ilegală, reintegrare, întârziere salariu, compensații.',
		'content' => '<p>Încălcările drepturilor salariaților — concediere ilegală, întârziere la salariu, refuzul concediului sau al compensațiilor — sunt situații frecvente care pot fi rezolvate cu ajutorul unui avocat. Biroul de avocatură Taras Molochko reprezentă interesele salariaților și angajatorilor în litigii de muncă: de la soluționare extrajudiciară până la reintegrare în instanță și încasarea salariului.</p>
<p>Pregătim acțiuni, plângeri la inspecția muncii și reprezentăm clientul în instanță. O solicitare la timp păstrează dovezile și crește șansele de rezultat pozitiv.</p>',
	),
	'Адвокат по кредитах' => array(
		'title'   => 'Avocat credite',
		'excerpt' => 'Litigii cu băncile, restructurare datorii, contestarea contractelor de credit și a hotărârilor instanței.',
		'content' => '<p>Datoriile la credit, executarea silită de către bănci, dobânzile excesive sau încălcarea condițiilor contractuale pot fi contestate. Biroul de avocatură Taras Molochko oferă asistență în litigiile cu băncile: analiza contractului de credit pentru încălcări, restructurarea datoriilor, contestarea hotărârilor instanței și protecția averii împotriva executării.</p>
<p>O consultație vă va permite să vă evaluați opțiunile și să alegeți o strategie de reducere a sarcinii datoriei în cadrul legii.</p>',
	),
	'Адвокат по корпоративних справах' => array(
		'title'   => 'Avocat afaceri corporative',
		'excerpt' => 'Înregistrare SRL și modificări, conflicte corporative, protecția drepturilor asociaților.',
		'content' => '<p>Relațiile corporative între asociații SRL, modificări în structură și conducere, înregistrarea companiilor și modificărilor la registrul de stat necesită precizie juridică. Biroul de avocatură Taras Molochko oferă asistență la înregistrarea afacerii, depunerea modificărilor, soluționarea conflictelor corporative și protecția drepturilor asociaților și conducerii.</p>
<p>Vă ajutăm cu pregătirea deciziilor, contractelor și reprezentarea intereselor în instanță și la autoritățile publice.</p>',
	),
	'Адвокат по господарських справах' => array(
		'title'   => 'Avocat litigii comerciale',
		'excerpt' => 'Contracte, încasarea datoriilor, faliment, lucrări de pretenții.',
		'content' => '<p>Litigiile comerciale — neexecutarea contractelor, încasarea creanțelor, falimentul partenerilor — sunt familiare oricărui business. Biroul de avocatură Taras Molochko oferă lucrări de pretenții, pregătire și analiză contracte, reprezentare în instanțele comerciale și asistență în procedurile de faliment.</p>
<p>Vă ajutăm să încasați datoriile, să vă protejați împotriva cererilor neîntemeiate și să găsiți soluția optimă în situații comerciale complexe.</p>',
	),
	'Допомога в отриманні ліцензії' => array(
		'title'   => 'Asistență pentru obținerea licențelor',
		'excerpt' => 'Pregătirea documentelor și asistență la obținerea licențelor și autorizațiilor pentru business.',
		'content' => '<p>Obținerea unei licențe sau autorizație pentru o anumită activitate necesită cunoașterea cerințelor legale și documente corect pregătite. Biroul de avocatură Taras Molochko oferă consultanță privind condițiile de obținere a licențelor, pregătirea pachetului de documente și însoțește clientul la etapa depunerii și comunicării cu autoritățile de licențiere.</p>
<p>Asistența juridică la timp reduce riscul refuzurilor și întârzierilor la obținerea autorizațiilor necesare.</p>',
	),
	'Абонентське юридичне обслуговування' => array(
		'title'   => 'Asistență juridică abonament',
		'excerpt' => 'Asistență juridică continuă pentru business: consultanță, contracte, pretenții, reprezentare.',
		'content' => '<p>Business-ul are nevoie de suport juridic constant: verificare contracte, consultanță privind activitatea, lucrări de pretenții și reprezentare la autorități și în instanță. Biroul de avocatură Taras Molochko oferă servicii pe bază de abonament — primiți un volum fix de consultanță și asistență lunar conform contractului.</p>
<p>Este convenabil pentru întreprinderile mici și mijlocii: știți costul suportului juridic din timp și vă puteți adresa cu întrebări în limitele pachetului.</p>',
	),
	'Аграрне право' => array(
		'title'   => 'Dreptul agrar',
		'excerpt' => 'Probleme funciare și agricole ale business-ului agrar, închiriere terenuri, contracte.',
		'content' => '<p>Business-ul agrar este legat de terenuri agricole, închiriere, contracte cu partenerii și reglementări de stat. Biroul de avocatură Taras Molochko oferă consultanță și asistență în dreptul agrar: formalizarea drepturilor asupra terenului, contracte de închiriere, litigii cu partenerii și protecția intereselor în timpul controalelor.</p>
<p>Vă ajutăm să respectați cerințele legale și să vă apărați afacerea în instanță și la autoritățile publice.</p>',
	),
	'Митне право' => array(
		'title'   => 'Dreptul vamal',
		'excerpt' => 'Formalități vamale, litigii cu vama, zone și proceduri, contestarea deciziilor.',
		'content' => '<p>Importul și exportul de mărfuri necesită respectarea procedurilor vamale și formalizarea corectă. Biroul de avocatură Taras Molochko oferă asistență la formalitățile vamale, contestarea deciziilor vamale, litigii privind plățile vamale și consultanță privind zonele și procedurile.</p>
<p>Asistența juridică la timp ajută la evitarea întârzierilor, amenzi și confiscări la trecerea frontierei și controlul vamal.</p>',
	),
	'Договірне право' => array(
		'title'   => 'Dreptul contractual',
		'excerpt' => 'Întocmirea și contestarea contractelor, pretenții, încasarea pe baza contractelor.',
		'content' => '<p>Contractele stau la baza relațiilor comerciale. Erorile în condiții sau neexecutarea de către partener poate duce la pierderi. Biroul de avocatură Taras Molochko oferă analiză și întocmire contracte, lucrări de pretenții, încasarea datoriilor contractuale și reprezentare în instanță la contestarea înțelegerilor sau la cererea executării obligațiilor.</p>
<p>O consultație vă va ajuta să evaluați riscurile contractului și să vă protejați interesele în condițiile acordului.</p>',
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
