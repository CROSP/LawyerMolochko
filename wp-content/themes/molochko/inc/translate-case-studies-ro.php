<?php
/**
 * One-time: translate all molochko-case-study RO posts (title, excerpt, content).
 * Run after you have created RO copies of the UA case studies.
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/translate-case-studies-ro.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/translate-case-studies-ro.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! function_exists( 'pll_get_post' ) ) {
	echo "Polylang is required.\n";
	exit( 1 );
}

// UA title => RO (title, excerpt, content)
$ro_case_studies = array(
	'Оскарження розшуку ТЦК та відновлення прав' => array(
		'title'   => 'Contestarea declanșării în căutare de TCC și restabilirea drepturilor',
		'excerpt' => 'Însoțire completă a cauzei: verificarea temeiurilor legale ale declanșării în căutare, adunarea dovezilor, reprezentarea intereselor în instanță. Decizia TCC a fost recunoscută ilegală.',
		'content' => '<p>Un client rezervist a fost declarat în căutare de centrul teritorial de recrutare fără temeiuri suficiente. Avocații biroului au făcut o evaluare juridică a materialelor, au adunat dovezi ale încălcării procedurii și au depus o acțiune administrativă în instanță.</p><p>Instanța a recunoscut decizia TCC ca fiind ilegală, declanșarea în căutare a fost anulată, drepturile clientului au fost restabilite. Reprezentarea intereselor s-a desfășurat fără prezența obligatorie a clientului în oraș — toate etapele au fost coordonate la distanță.</p><p>Dacă sunteți în căutare de TCC sau vă îndoiți de legalitatea acțiunilor comisariatului — contactați-ne pentru o consultație. Vom verifica temeiurile și vă vom ajuta să vă apărați drepturile în cadrul legislației în vigoare.</p>',
	),
	'Оскарження рішення ВЛК: звільнення з військової служби' => array(
		'title'   => 'Contestarea deciziei VLC: eliberare din serviciul militar',
		'excerpt' => 'Însoțirea contestării hotărârii comisiei medico-militare în procedura pre-judiciară și judiciară. S-a obținut recunoașterea inaptitudinii pentru serviciu din motive de sănătate.',
		'content' => '<p>Clientul nu era de acord cu concluzia comisiei medico-militare (VLC) privind gradul de aptitudine pentru serviciul militar. Termenul de contestare a hotărârii VLC este limitat, de aceea este important să contactați un avocat imediat după primirea documentelor.</p><p>Am pregătit o plângere în procedura pre-judiciară cu anexarea setului complet de documente medicale, iar după refuz — acțiune administrativă în instanță. Instanța a verificat respectarea procedurii VLC, caracterul complet al examinării și motivarea hotărârii și a recunoscut decizia ca fiind ilegală. Clientul a fost eliberat din serviciul militar din motive de sănătate.</p><p>Contestarea VLC necesită cunoașterea procedurilor și termenelor. Oferim consultanță privind contestarea pre-judiciară și reprezentarea intereselor în instanță.</p>',
	),
	'Відстрочка від мобілізації: законні підстави' => array(
		'title'   => 'Amânarea de la mobilizare: temeiuri legale',
		'excerpt' => 'Formalizarea amânării pe baza legii privind mobilizarea. Adunarea documentelor, pregătirea pentru prezentarea la TCC și VLC, însoțire în toate etapele.',
		'content' => '<p>Clientul rezervist avea dreptul la amânare din motive familiale și de sănătate, însă centrul teritorial de recrutare refuza să o acorde. Avocații biroului au ajutat la adunarea documentelor necesare, la întocmirea cererii și la pregătirea clientului pentru prezentarea la TCC și trecerea VLC.</p><p>Amânarea a fost acordată în întregime pe baza legislației în vigoare. Clientul a primit explicații clare privind drepturile și obligațiile sale, ceea ce a permis evitarea riscurilor și sancțiunilor inutile.</p><p>Oferim consultanță privind amânarea, actualizarea datelor personale, predarea citațiilor și răspunderea pentru neprezentare. Asistența rezerviștilor este una dintre direcțiile prioritare ale biroului.</p>',
	),
	'Відшкодування збитків після ДТП: стягнення повної компенсації' => array(
		'title'   => 'Despăgubiri după accident rutier: încasarea compensației integrale',
		'excerpt' => 'Reprezentarea intereselor victimei într-un accident rutier. Contestarea refuzului asiguratorului de plată, încasarea daunelor materiale și a prejudiciului moral prin instanță.',
		'content' => '<p>Clientul a fost rănit într-un accident rutier. Compania de asigurări refuza despăgubirea integrală, invocând nuanțe tehnice ale contractului. Avocații biroului au analizat materialele cauzei, au adunat dovezi privind cuantumul daunelor și au pregătit acțiunea în instanță.</p><p>Instanța a recunoscut cererile clientului ca fiind legitime: de la autorul accidentului și de la compania de asigurări s-a încasat compensația integrală pentru daune materiale și prejudiciu moral. Reprezentarea intereselor victimei s-a desfășurat în toate etapele — de la lucrările de pretenții până la procedura de executare.</p><p>Asistență în cazul accidentelor rutiere: protecția victimelor, contestarea refuzurilor de despăgubire asigurată, litigii cu asiguratorii. O consultație vă permite să evaluați perspectivele cauzei înainte de a apela la instanță.</p>',
	),
	'Захист у кримінальній справі: закриття провадження' => array(
		'title'   => 'Apărare în cauza penală: închiderea procedurii',
		'excerpt' => 'Apărare în faza de urmărire penală și în instanță. Cauza închisă pentru lipsa elementelor infracțiunii în urma reprezentării intereselor clientului.',
		'content' => '<p>Clientului i s-a comunicat că este suspectat de săvârșirea unei infracțiuni. Apărarea drepturilor și intereselor clientului în faza de urmărire penală are o importanță decisivă — atunci se formează baza probatorie a cauzei.</p><p>Avocații biroului au studiat materialele cauzei, le-au dat o evaluare juridică și au pregătit cererea de închidere a procedurii penale. S-a asigurat apărarea în timpul acțiunilor de urmărire și a interogatoriilor. După judecată, cauza a fost închisă pentru lipsa elementelor infracțiunii.</p><p>Oferim asistență completă în cauze penale: consultație, apărare la reținere, reprezentare în instanță, contestarea sentințelor. Evaluăm onest perspectivele și informăm clientul despre desfășurarea cauzei.</p>',
	),
	'Розірвання шлюбу та розподіл майна' => array(
		'title'   => 'Divorț și împărțirea averii',
		'excerpt' => 'Divorț în instanță, împărțirea averii comune ținând cont de contribuția părților, stabilirea locuinței minorilor și a pensiei alimentare.',
		'content' => '<p>Clientul s-a adresat privind divorțul și împărțirea averii comune. Cealaltă parte nu accepta un acord pe cale amiabilă, astfel că soluționarea în instanță a fost singura cale de a apăra interesele clientului.</p><p>Avocații biroului au reprezentat interesele clientului în instanță: căsătoria a fost desfăcută, averea a fost împărțită ținând cont de contribuția fiecărui soț, s-a stabilit locuința minorilor și pensia alimentară. Clientul a obținut un rezultat corespunzător așteptărilor și temeiurilor legale.</p><p>Cauze familiale: divorț, pensie alimentară — încasare și încetare, despuberarea drepturilor părintești, tutelă și curatelă, stabilirea locuinței și a programului de legături cu copilul. Consultație și însoțire în instanță.</p>',
	),
	'Відновлення на роботі після незаконного звільнення' => array(
		'title'   => 'Reintegrare la serviciu după concediere ilegală',
		'excerpt' => 'Recunoașterea concedierii ca fiind ilegală, reintegrare în funcție și încasarea salariului pentru perioada de absență forțată. Reprezentare în instanță în litigii de muncă.',
		'content' => '<p>Salariatul a fost concediat cu încălcarea procedurii și fără temeiuri corespunzătoare. Clientul s-a adresat biroului pentru a contesta concedierea și a fi reintegrat la serviciu.</p><p>Avocații au analizat documentele, au pregătit acțiunea în instanță pentru recunoașterea concedierii ca fiind ilegală și reintegrare la serviciu. Instanța a admis acțiunea în întregime: clientul a fost reintegrat în funcție, de la angajator s-a încasat salariul pentru întreaga perioadă de absență forțată. Reprezentarea intereselor clientului în instanță s-a desfășurat profesional și cu respectarea termenelor.</p><p>Litigii de muncă: concediere ilegală, reducerea personalului, discriminare, întârziere la salariu. Consultație și însoțire în negocieri cu angajatorul și în instanță.</p>',
	),
	'Повістка ТЦК та неявка: консультація та супровід' => array(
		'title'   => 'Citație TCC și neprezentare: consultație și însoțire',
		'excerpt' => 'Clarificarea obligațiilor rezerviștilor, pregătirea pentru prezentarea la citație și evitarea sancțiunilor nejustificate. Acționăm în cadrul legii privind mobilizarea.',
		'content' => '<p>Clientul a primit o citație de la TCC și nu era sigur dacă este obligat să o execute și ce consecințe sunt prevăzute pentru neprezentare. Legea privind mobilizarea reglementează procedura de predare a citațiilor, actualizarea datelor și răspunderea pentru încălcări.</p><p>Avocații biroului i-au clarificat clientului prevederile legale, l-au ajutat să pregătească documentele necesare și l-au însoțit la prezentare. Clientul și-a îndeplinit obligațiile în limitele legale fără amenzi sau alte sancțiuni. Informarea constantă despre desfășurarea cauzei i-a permis clientului să acționeze cu încredere.</p><p>Consultanțe actuale privind citațiile, actualizarea datelor la TCC, amenzi și răspunderea administrativă. Acționăm exclusiv în cadrul legislației în vigoare și oferim o evaluare onestă a perspectivelor.</p>',
	),
);

$pll_default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
$ua_posts = get_posts( array(
	'post_type'      => 'molochko-case-study',
	'posts_per_page' => -1,
	'post_status'    => 'any',
	'lang'           => $pll_default ? $pll_default : '',
) );

$updated = 0;
foreach ( $ua_posts as $post ) {
	$ua_title = $post->post_title;
	if ( ! isset( $ro_case_studies[ $ua_title ] ) ) {
		continue;
	}
	$ro_post_id = (int) pll_get_post( $post->ID, 'ro' );
	if ( ! $ro_post_id ) {
		echo "No RO translation for: {$ua_title} (ID: {$post->ID}). Create RO copy in Polylang.\n";
		continue;
	}
	$ro = $ro_case_studies[ $ua_title ];
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

echo "Done. Translated {$updated} case study post(s) to Romanian.\n";
