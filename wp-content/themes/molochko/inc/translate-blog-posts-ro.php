<?php
/**
 * One-time: translate blog post RO copies (title, excerpt, content).
 * Matches posts by UA title from seed-blog-posts.php. Run after RO copies exist.
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/translate-blog-posts-ro.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/translate-blog-posts-ro.php
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
$ro_posts = array(
	'Як вести себе з поліцією при затриманні: права та поради' => array(
		'title'   => 'Cum să vă purtați cu poliția la reținere: drepturi și sfaturi',
		'excerpt' => 'Ce să faceți la contact cu poliția, ce drepturi aveți și ce e bine să evitați. Sfaturi de la avocat pentru situații de reținere și control.',
		'content' => '<p>O întâlnire cu poliția este o situație stresantă pentru majoritatea oamenilor. Este important să știți: aveți dreptul să nu dați declarații până la sosirea avocatului, să nu semnați documente pe care nu le-ați citit și să cereți să se consemneze refuzul de a da declarații. Polițistul este obligat să se prezinte, să indice temeiul reținerii sau al controlului și să vă explice drepturile.</p><p>Dacă ați fost reținut — nu vă opuneți fizic, dar declarați clar că vă veți bucura de dreptul la apărare. Sunați un rudă sau un avocat cât mai curând. Nu acceptați o „scurtă discuție fără proces-verbal” — orice cuvinte ale dumneavoastră pot fi folosite. Avocatul vă ajută să controlați procedura și să vă pregătiți poziția corectă.</p><p>Biroul nostru oferă consultanță privind interacțiunea cu poliția și organele de aplicare a legii, apărarea la reținere și în timpul acțiunilor de urmărire. Contactați-ne imediat după incident.</p>',
	),
	'Адвокатський запит до ТЦК: на що має право адвокат' => array(
		'title'   => 'Cererea avocatului către TCC: ce drepturi are avocatul',
		'excerpt' => 'Poate un avocat adresa cereri centrului teritorial de recrutare și ce informații sunt obligați să furnizeze. Practică și limitări.',
		'content' => '<p>Avocatul are dreptul să se adreseze organelor de stat, inclusiv TCC, cu cereri privind cauzele clientului în cadrul asistenței juridice. Cererea se depune în formă scrisă, cu referire la legea privind profesia de avocat și cu obiectul cererii: obținerea de copii ale documentelor, clarificarea datei prezentării, temeiurilor pentru includerea în căutare etc.</p><p>TCC este obligat să examineze cererea în termenul stabilit și să răspundă sau să refuze motivat. Refuzul poate fi contestat. Este important: cererea avocatului nu înlocuiește prezentarea rezervistului la citație și nu constituie temei pentru neprezentare dacă prezentarea este obligatorie. Avocatul vă ajută să obțineți datele necesare pentru pregătirea la prezentare sau contestarea deciziilor.</p><p>Oferim consultanță privind interacțiunea cu TCC, cererile avocaților și protecția drepturilor rezerviștilor. Vă ajutăm să pregătiți cererea și să evaluați răspunsul TCC.</p>',
	),
	'Стягнення боргу через суд: претензія, позов, виконання' => array(
		'title'   => 'Încasarea datoriei prin instanță: pretenție, acțiune, executare',
		'excerpt' => 'Cum să încasați bani de la debitor pe cale legală: procedura de pretenții, pregătirea acțiunii și încasarea banilor conform hotărârii judecătorești.',
		'content' => '<p>Dacă vi se datorează bani în baza unui contract sau ca urmare a producerii unui prejudiciu, primul pas este trimiterea unei pretenții debitorului cu cererea de a achita datoria în termenul stabilit. Pretenția fixează suma, temeiurile și oferă debitorului posibilitatea de a-și îndeplini obligația în mod voluntar. Dacă datoria nu a fost achitată — vă puteți adresa instanței cu o acțiune de încasare a datoriei.</p><p>În acțiune se indică suma datoriei principale, penalitățile sau dobânzile conform legii (dacă sunt prevăzute), cheltuielile cu avocatul. După intrarea în vigoare a hotărârii judecătorești se eliberează titlul executoriu, care se transmite executorului judecătoresc sau privat. Executorul deschide procedura și asigură încasarea din venituri sau din averea debitorului.</p><p>Biroul nostru oferă asistență la încasarea datoriilor: întocmirea pretențiilor, pregătirea acțiunilor, reprezentare în instanță și însoțirea procedurii de executare.</p>',
	),
	'Аліменти: як стягнути та змінити розмір' => array(
		'title'   => 'Pensie alimentară: cum să o încasați și să modificați cuantumul',
		'excerpt' => 'Cum să încasați pensia alimentară prin executor, cum să modificați cuantumul prin instanță și ce să faceți dacă plătitorul evită plățile.',
		'content' => '<p>Pensia alimentară pentru întreținerea copilului trebuie plătită de persoana care nu locuiește cu copilul. Cuantumul poate fi stabilit prin acordul părinților sau prin hotărârea instanței. Dacă plătitorul nu plătește, beneficiarul se poate adresa executorului judecătoresc sau privat cu titlul executoriu — executorul încasează suma din venituri sau din averea debitorului.</p><p>Modificarea cuantumului pensiei alimentare (mărirea sau micșorarea) se poate face prin instanță în cazul schimbării circumstanțelor: schimbarea veniturilor, starea de sănătate, apariția altor persoane care trebuie întreținute. Dacă plătitorul evită plățile sau ascunde veniturile, se poate pune problema încasării penalităților și a tragerii la răspundere.</p><p>Oferim consultanță privind pensia alimentară: încasare, modificarea cuantumului, contestarea hotărârilor. Vă ajutăm să pregătiți documentele și să vă reprezentăm interesele în instanță și în procedura de executare.</p>',
	),
	'Обшук у помешканні: права та порядок проведення' => array(
		'title'   => 'Percheziția în locuință: drepturi și procedură',
		'excerpt' => 'Când poate poliția efectua o percheziție, ce drepturi aveți în timpul percheziției și ce să faceți dacă procedura a fost încălcată.',
		'content' => '<p>Percheziția în locuință este permisă doar pe baza hotărârii instanței, cu excepția cazurilor de urgență, când procurorul întocmește o ordonanță cu aprobarea ulterioară a instanței. În ordonanță trebuie indicate temeiurile, obiectul căutării și adresa. Este obligatorie prezența martorilor; aveți dreptul să cereți prezența avocatului.</p><p>În timpul percheziției nu sunteți obligat să ajutați la căutare, dar nici să nu împiedicați. Aveți dreptul să consultați procesul-verbal și să faceți observații. Dacă considerați că percheziția este ilegală sau a fost efectuată cu încălcări, consemnați acest lucru în observațiile la procesul-verbal și contactați un avocat pentru contestarea acțiunilor și a dovezilor obținute.</p><p>Oferim consultanță și apărare în cauzele în care au avut loc percheziții. Vă ajutăm să evaluați legalitatea procedurii și să pregătiți plângeri și cereri.</p>',
	),
	'Європротокол при ДТП: коли можна оформити без поліції' => array(
		'title'   => 'Europrotocol la accident rutier: când se poate întocmi fără poliție',
		'excerpt' => 'În ce cazuri accidentul rutier poate fi înregistrat prin europrotocol fără chemarea poliției și cum să procedați corect.',
		'content' => '<p>Europrotocolul este înregistrarea simplificată a accidentului rutier fără participarea poliției, când coliziunea a avut loc între doi participanți, ambele autovehicule sunt asigurate, nu există răniți și suma daunelor nu depășește limita stabilită. Participanții completează singuri formularul de notificare a accidentului, consemnează circumstanțele și se adresează asiguratorului.</p><p>Este important să completați corect schema și să indicați vinovatul; erorile pot duce la refuzul plății. După întocmirea europrotocolului trebuie să depuneți documentele companiei de asigurări în termenul stabilit. Dacă există neconcordanțe privind circumstanțele sau vinovatul, este mai bine să chemați poliția.</p><p>Oferim consultanță privind accidentele rutiere, plățile de asigurare și contestarea refuzurilor. Vă ajutăm să evaluați dacă situația dumneavoastră se potrivește pentru europrotocol și cum să procedați mai departe.</p>',
	),
	'Спадщина за кордоном: оформлення та нюанси' => array(
		'title'   => 'Moștenirea în străinătate: formalizare și nuanțe',
		'excerpt' => 'Cum să formalizați moștenirea în străinătate: documente, apostilă, termene și competența notarului ucrainean.',
		'content' => '<p>Dacă moștenitorul sau bunurile succesorale se află în străinătate, moștenitorii trebuie să respecte atât legislația ucraineană, cât și cea străină. Adesea este nevoie de obținerea documentelor din țara în care se află bunurile (certificat de deces, testament, documente pentru bunuri), legalizarea sau apostilarea acestora, traducere.</p><p>În Ucraina se poate deschide dosarul succesoral la notar pentru fixarea dreptului la cota din moștenire; pentru acceptarea bunurilor în străinătate sunt de obicei necesare acțiuni în țara în care se află bunurile (notar, consul, avocat local). Termenele de acceptare a moștenirii diferă în funcție de țară.</p><p>Oferim consultanță privind moștenirea internațională, pregătirea documentelor și interacțiunea cu notarii și consulatele străine.</p>',
	),
	'Кримінальна справа: етапи від підозри до вироку' => array(
		'title'   => 'Cauza penală: etape de la suspectare la sentință',
		'excerpt' => 'Cum evoluează cauza penală: de la notificarea despre suspectare și acțiunile de urmărire până la actul de acuzare și judecată.',
		'content' => '<p>Cauza penală trece prin mai multe etape: urmărirea penală (adunarea dovezilor, acțiunile de urmărire, notificarea despre suspectare, acuzare), pregătirea pentru judecată și procedura judecătorească până la sentință. În etapa urmăririi aveți dreptul la un avocat din momentul reținerii sau al notificării despre suspectare.</p><p>După încheierea urmăririi, procurorul întocmește actul de acuzare și cauza este trimisă la judecată. În instanță se examinează dovezile, se aud părțile; inculpatul poate recunoaște sau nu vinovăția. În funcție de rezultate, instanța pronunță sentința — de condamnare sau de achitare. Fiecare etapă are termenele și posibilitățile sale de apărare și contestare.</p><p>Oferim apărare în toate etapele procedurii penale: de la primele interogatorii până la contestarea sentinței. O consultație vă ajută să înțelegeți situația și să alegeți strategia.</p>',
	),
);

$pll_default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
$ua_posts = get_posts( array(
	'post_type'      => 'post',
	'posts_per_page' => -1,
	'post_status'    => 'any',
	'lang'           => $pll_default ? $pll_default : '',
) );

$updated = 0;
foreach ( $ua_posts as $post ) {
	$ua_title = $post->post_title;
	if ( ! isset( $ro_posts[ $ua_title ] ) ) {
		continue;
	}
	$ro_post_id = (int) pll_get_post( $post->ID, 'ro' );
	if ( ! $ro_post_id ) {
		echo "No RO translation for: {$ua_title} (ID: {$post->ID}). Create RO copy in Polylang.\n";
		continue;
	}
	$ro = $ro_posts[ $ua_title ];
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

echo "Done. Translated {$updated} blog post(s) to Romanian.\n";
