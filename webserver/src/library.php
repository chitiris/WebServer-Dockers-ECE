<?php
// src/library.php — Λίστα βιβλίων από τη βάση "bookstore"
//
// =====================================================================
//  ΠΩΣ ΣΥΝΔΕΕΤΑΙ Η ΣΕΛΙΔΑ ΜΕ ΤΗ ΒΑΣΗ ΔΕΔΟΜΕΝΩΝ
//  Ψάξτε στον κώδικα τα σχόλια [ΒΑΣΗ Χ]. Ακολουθούν τη σειρά:
//    [ΒΑΣΗ 1]  Στοιχεία σύνδεσης (host, χρήστης, κωδικός, βάση)
//    [ΒΑΣΗ 2]  Άνοιγμα σύνδεσης με τον MySQL server
//    [ΒΑΣΗ 3]  Κωδικοποίηση χαρακτήρων της σύνδεσης
//    [ΒΑΣΗ 4]  Απλό ερώτημα SQL (πλήθος βιβλίων)
//    [ΒΑΣΗ 5]  Ερώτημα με prepared statement (αναζήτηση/ταξινόμηση)
//    [ΒΑΣΗ 6]  Ανάκτηση των αποτελεσμάτων σε πίνακα PHP
//    [ΒΑΣΗ 7]  Κλείσιμο της σύνδεσης
//    [ΒΑΣΗ 8]  Χειρισμός σφαλμάτων της βάσης
//    [ΒΑΣΗ 9]  Εμφάνιση των δεδομένων της βάσης μέσα στο HTML
// =====================================================================

// ---- [ΒΑΣΗ 1] Στοιχεία σύνδεσης ----------------------------------------
// Ως host δεν γράφουμε "localhost" αλλά το όνομα του service "mysql" από
// το docker-compose.yml: τα containers βρίσκονται στο ίδιο δίκτυο Docker
// και το καθένα «βλέπει» τα άλλα με το όνομα του service τους.
// Ο χρήστης και ο κωδικός είναι αυτοί που ορίσαμε στο env/mysql.env
// (MYSQL_ROOT_PASSWORD), και η βάση "bookstore" δημιουργείται όταν
// κάνουμε import το bookstore.sql.
$servername = "mysql";
$username   = "root";
$password   = "123456";
$dbname     = "bookstore";

// Βοηθητική συνάρτηση για ασφαλή εμφάνιση κειμένου (προστασία από XSS)
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---- Παράμετροι αναζήτησης / ταξινόμησης από το URL --------------------
$search = trim($_GET['q'] ?? '');

// Επιτρέπουμε μόνο συγκεκριμένες τιμές ταξινόμησης (whitelist)
$sortOptions = [
    'title'  => ['label' => 'Τίτλος',            'sql' => 'title ASC'],
    'author' => ['label' => 'Συγγραφέας',        'sql' => 'author ASC, title ASC'],
    'newest' => ['label' => 'Νεότερα πρώτα',     'sql' => 'publication_year DESC, title ASC'],
    'oldest' => ['label' => 'Παλαιότερα πρώτα',  'sql' => 'publication_year ASC, title ASC'],
];
$sort = array_key_exists($_GET['sort'] ?? '', $sortOptions) ? $_GET['sort'] : 'title';

$books = [];
$error = null;
$total = 0;

// ---- Σύνδεση και ερωτήματα -----------------------------------------------
// Ζητάμε από την mysqli να «πετάει» exceptions σε κάθε σφάλμα της βάσης,
// ώστε να τα πιάνουμε όλα μαζί στο catch παρακάτω ([ΒΑΣΗ 8]).
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // ---- [ΒΑΣΗ 2] Άνοιγμα σύνδεσης -----------------------------------
    // Εδώ η PHP (container "php") συνδέεται μέσω δικτύου στον MySQL server
    // (container "mysql", θύρα 3306) με τα στοιχεία του [ΒΑΣΗ 1].
    // Το αντικείμενο $conn είναι η ανοιχτή σύνδεση· μέσα από αυτό
    // στέλνουμε όλα τα ερωτήματα SQL.
    $conn = new mysqli($servername, $username, $password, $dbname);

    // ---- [ΒΑΣΗ 3] Κωδικοποίηση χαρακτήρων ----------------------------
    // Λέμε στη βάση ότι θα ανταλλάσσουμε κείμενο σε utf8mb4,
    // αλλιώς τα ελληνικά εμφανίζονται ως «???».
    $conn->set_charset("utf8mb4");

    // ---- [ΒΑΣΗ 4] Απλό ερώτημα SQL -----------------------------------
    // Το query() στέλνει το SQL στη βάση και επιστρέφει το αποτέλεσμα.
    // Το fetch_row()[0] παίρνει την πρώτη στήλη της πρώτης γραμμής,
    // δηλαδή τον αριθμό που υπολόγισε το COUNT(*).
    $total = (int) $conn->query("SELECT COUNT(*) FROM books")->fetch_row()[0];

    // ---- [ΒΑΣΗ 5] Ερώτημα με prepared statement ----------------------
    // Το κείμενο αναζήτησης έρχεται από τον χρήστη, οπότε ΔΕΝ το κολλάμε
    // ποτέ απευθείας μέσα στο SQL (κίνδυνος SQL injection).
    // Γράφουμε ? στη θέση του και το περνάμε χωριστά με bind_param.
    $sql  = "SELECT id, title, author, publication_year FROM books
             WHERE title LIKE ? OR author LIKE ?
             ORDER BY " . $sortOptions[$sort]['sql']; // μόνο τιμές από τη whitelist
    $stmt = $conn->prepare($sql);          // η βάση «προετοιμάζει» το ερώτημα
    $like = '%' . $search . '%';           // % = οποιοιδήποτε χαρακτήρες (LIKE)
    $stmt->bind_param("ss", $like, $like); // "ss" = δύο παράμετροι τύπου string
    $stmt->execute();                      // εκτέλεση στον MySQL server

    // ---- [ΒΑΣΗ 6] Ανάκτηση αποτελεσμάτων -----------------------------
    // Οι γραμμές του πίνακα books γίνονται πίνακας PHP. Κάθε στοιχείο
    // είναι ένα βιβλίο με κλειδιά τα ονόματα των στηλών, π.χ.
    // $books[0]['title'], $books[0]['author'], $books[0]['publication_year'].
    $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // ---- [ΒΑΣΗ 7] Κλείσιμο σύνδεσης ----------------------------------
    // Τα δεδομένα είναι πλέον στη μεταβλητή $books, οπότε δεν χρειαζόμαστε
    // άλλο τη βάση· ελευθερώνουμε τη σύνδεση.
    $stmt->close();
    $conn->close();
} catch (mysqli_sql_exception $ex) {
    // ---- [ΒΑΣΗ 8] Χειρισμός σφαλμάτων --------------------------------
    // Αν αποτύχει οποιοδήποτε βήμα 2–6 (π.χ. δεν τρέχει το container
    // mysql, λάθος κωδικός, δεν έχει γίνει import), ερχόμαστε εδώ.
    // 1049 = Unknown database, 1146 = Table doesn't exist
    if (in_array($ex->getCode(), [1049, 1146], true)) {
        $error = "Η βάση «bookstore» δεν βρέθηκε. Κάντε import το αρχείο bookstore.sql από το phpMyAdmin (http://localhost:8085).";
    } else {
        $error = "Σφάλμα σύνδεσης με τη βάση δεδομένων: " . $ex->getMessage();
    }
}

// Ένα «εξώφυλλο» με τα αρχικά του τίτλου και χρώμα που προκύπτει από τον τίτλο
function coverColor(string $title): int
{
    return crc32($title) % 360;
}

function initials(string $title): string
{
    $words = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', '', $title), -1, PREG_SPLIT_NO_EMPTY);
    $letters = array_map(fn($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2));
    return mb_strtoupper(implode('', $letters));
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Βιβλιοθήκη · Docker Lab</title>
    <style>
        :root {
            --bg: #f5f6f8;
            --surface: #ffffff;
            --ink: #1f2933;
            --muted: #6b7280;
            --line: #e5e7eb;
            --accent: #f5b800;      /* το κίτρινο των διαφανειών */
            --accent-ink: #3d2e00;
            --radius: 12px;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #111418;
                --surface: #1a1f25;
                --ink: #e8eaed;
                --muted: #9aa3ad;
                --line: #2b323a;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.5;
        }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
        }
        header .wrap {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; padding-top: 20px; padding-bottom: 20px; flex-wrap: wrap;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo {
            width: 44px; height: 44px; border-radius: 10px;
            background: var(--accent); color: var(--accent-ink);
            display: grid; place-items: center; font-size: 22px; font-weight: 700;
        }
        h1 { font-size: 1.5rem; margin: 0; }
        .subtitle { margin: 0; color: var(--muted); font-size: .9rem; }
        .stat {
            background: var(--bg); border: 1px solid var(--line);
            border-radius: 999px; padding: 6px 14px; font-size: .9rem; color: var(--muted);
        }
        .stat strong { color: var(--ink); }

        form.toolbar {
            display: flex; gap: 10px; flex-wrap: wrap; margin: 28px 0 8px;
        }
        .toolbar input[type="search"], .toolbar select {
            font: inherit; color: var(--ink); background: var(--surface);
            border: 1px solid var(--line); border-radius: 10px; padding: 10px 14px;
        }
        .toolbar input[type="search"] { flex: 1 1 280px; }
        .toolbar input:focus, .toolbar select:focus { outline: 2px solid var(--accent); outline-offset: 1px; }
        .toolbar button {
            font: inherit; font-weight: 600; cursor: pointer;
            background: var(--accent); color: var(--accent-ink);
            border: 0; border-radius: 10px; padding: 10px 20px;
        }
        .toolbar a.reset { align-self: center; color: var(--muted); font-size: .9rem; }

        .results-info { color: var(--muted); font-size: .9rem; margin: 8px 0 20px; }

        .grid {
            display: grid; gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            padding-bottom: 40px;
        }
        .book {
            background: var(--surface); border: 1px solid var(--line);
            border-radius: var(--radius); padding: 16px;
            display: flex; gap: 14px; align-items: flex-start;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .book:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); }
        .cover {
            flex: 0 0 56px; height: 78px; border-radius: 6px;
            display: grid; place-items: center;
            color: #fff; font-weight: 700; font-size: 1.1rem; letter-spacing: .5px;
            box-shadow: inset -4px 0 0 rgba(0,0,0,.15);
        }
        .book h2 { font-size: 1rem; margin: 0 0 4px; line-height: 1.3; }
        .book .author { margin: 0; color: var(--muted); font-size: .9rem; }
        .year {
            display: inline-block; margin-top: 10px; font-size: .8rem; font-weight: 600;
            background: var(--bg); border: 1px solid var(--line);
            border-radius: 999px; padding: 2px 10px;
        }

        .empty, .error {
            background: var(--surface); border: 1px solid var(--line);
            border-radius: var(--radius); padding: 28px; text-align: center; color: var(--muted);
        }
        .error { border-color: #e5a50a; color: var(--ink); margin-top: 28px; }

        footer { color: var(--muted); font-size: .8rem; padding: 20px 0 40px; text-align: center; }
        footer code { background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: 1px 6px; }
    </style>
</head>
<body>

<header>
    <div class="wrap">
        <div class="brand">
            <div class="logo" aria-hidden="true">📚</div>
            <div>
                <h1>Βιβλιοθήκη</h1>
                <p class="subtitle">Δεδομένα από τη βάση MySQL <code>bookstore</code></p>
            </div>
        </div>
        <?php /* [ΒΑΣΗ 9] Το πλήθος που υπολόγισε η βάση στο [ΒΑΣΗ 4] */ ?>
        <?php if (!$error): ?>
            <span class="stat"><strong><?= $total ?></strong> βιβλία στη βάση</span>
        <?php endif; ?>
    </div>
</header>

<main class="wrap">
    <?php /* [ΒΑΣΗ 9] Αν η σύνδεση απέτυχε, δείχνουμε το μήνυμα του [ΒΑΣΗ 8] */ ?>
    <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
    <?php else: ?>

        <form class="toolbar" method="get" role="search">
            <input type="search" name="q" value="<?= e($search) ?>"
                   placeholder="Αναζήτηση τίτλου ή συγγραφέα…" aria-label="Αναζήτηση">
            <select name="sort" aria-label="Ταξινόμηση">
                <?php foreach ($sortOptions as $key => $opt): ?>
                    <option value="<?= e($key) ?>" <?= $key === $sort ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Αναζήτηση</button>
            <?php if ($search !== '' || $sort !== 'title'): ?>
                <a class="reset" href="library.php">Καθαρισμός</a>
            <?php endif; ?>
        </form>

        <p class="results-info">
            <?php if ($search !== ''): ?>
                <?= count($books) ?> αποτελέσματα για «<?= e($search) ?>»
            <?php else: ?>
                Εμφανίζονται όλα τα βιβλία
            <?php endif; ?>
        </p>

        <?php if (count($books) === 0): ?>
            <div class="empty">Δεν βρέθηκαν βιβλία.</div>
        <?php else: ?>
            <section class="grid">
                <?php /* [ΒΑΣΗ 9] Για κάθε γραμμή του πίνακα books (από το [ΒΑΣΗ 6])
                         φτιάχνουμε μία κάρτα. Οι τιμές $book['title'], $book['author']
                         και $book['publication_year'] είναι οι στήλες της βάσης. */ ?>
                <?php foreach ($books as $book): ?>
                    <article class="book">
                        <div class="cover" style="background: hsl(<?= coverColor($book['title']) ?> 45% 42%)" aria-hidden="true">
                            <?= e(initials($book['title'])) ?>
                        </div>
                        <div>
                            <h2><?= e($book['title']) ?></h2>
                            <p class="author"><?= e($book['author']) ?></p>
                            <?php if ($book['publication_year']): ?>
                                <span class="year"><?= e($book['publication_year']) ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

    <?php endif; ?>
</main>

<footer>
    Docker Lab · <code>nginx</code> + <code>php-fpm</code> + <code>mysql</code> · PHP <?= e(PHP_VERSION) ?>
</footer>

</body>
</html>
