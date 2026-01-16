<?php
// ==========================================
// PHP: 邏輯更新
// ==========================================
$baseDir = '2B';

function getBooks($dir) {
    $books = [];
    if (!is_dir($dir)) return $books;

    $groups = scandir($dir);
    foreach ($groups as $group) {
        if ($group === '.' || $group === '..') continue;
        
        $groupPath = $dir . '/' . $group;
        if (is_dir($groupPath)) {
            $allImages = glob($groupPath . '/*.png');
            $coverPath = $groupPath . '/b.png';
            
            // 過濾出內頁 (排除 b.png)
            $contentPages = array_filter($allImages, function($path) {
                return basename($path) !== 'b.png';
            });
            
            // 內頁排序
            natsort($contentPages);
            $contentPages = array_values($contentPages);

            // 封面設定：優先使用 b.png，沒有則用第一張內頁
            $finalCover = file_exists($coverPath) ? $coverPath : ($contentPages[0] ?? '');

            if (!empty($contentPages)) {
                $books[] = [
                    'id' => $group,
                    'title' => $group,       // <--- 修改處：直接使用資料夾名稱
                    'cover' => $finalCover,  // 書架顯示 b.png
                    'pages' => $contentPages // Zoom 進去後從 1.png 開始
                ];
            }
        }
    }
    return $books;
}

$library = getBooks($baseDir);
?>

<!DOCTYPE html>
<html lang="zh-HK">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI文言故事漫畫創作</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Ma+Shan+Zheng&family=Noto+Serif+TC:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        /* =========================================
           1. 中國風全局設定
           ========================================= */
        :root {
            --wood-dark: #3e2723;   /* 紫檀木色 */
            --wood-light: #5d4037;  /* 花梨木色 */
            --paper-bg: #fdf5e6;    /* 宣紙色 */
            --gold: #ffecb3;        /* 金漆 */
        }

        body {
            background-color: var(--paper-bg);
            /* 宣紙紋理 */
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23d7ccc8' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
            font-family: 'Noto Serif TC', serif;
            margin: 0;
            padding: 0;
            color: var(--wood-dark);
            min-height: 100vh;
        }

        /* 標題區：牌匾 */
        header {
            text-align: center;
            padding: 60px 20px 40px;
        }

        .plaque {
            display: inline-block;
            background: var(--wood-dark);
            padding: 15px 50px;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            border: 4px solid #8d6e63;
        }

        h1 {
            font-family: 'Ma Shan Zheng', cursive;
            font-size: 3.5rem;
            margin: 0;
            color: var(--gold);
            text-shadow: 2px 2px 0px rgba(0,0,0,0.5);
            letter-spacing: 5px;
        }

        .subtitle {
            margin-top: 20px;
            font-size: 1.1rem;
            color: #5d4037;
            font-weight: bold;
        }

        /* =========================================
           2. 博古架 (Grid)
           ========================================= */
        .shelf-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            background: #4e342e;
            border-radius: 4px;
            box-shadow: inset 0 0 30px rgba(0,0,0,0.6), 0 20px 50px rgba(0,0,0,0.3);
            border: 8px solid #3e2723;
            
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }

        /* 格子 */
        .shelf-cell {
            background-color: #3e2723;
            border: 6px solid #6d4c41;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.7);
            padding: 25px 20px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            position: relative;
            height: 340px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .shelf-cell:hover {
            background-color: #4a2f2a;
            box-shadow: inset 0 0 25px rgba(0,0,0,0.5);
        }

        /* =========================================
           3. 線裝書裝飾
           ========================================= */
        .book-wrapper {
            position: relative;
            width: 100%;
            max-width: 180px;
            transition: transform 0.3s;
        }

        /* 書脊 (藍色條+白線) */
        .book-spine-deco {
            position: absolute;
            top: 2px; left: 0; bottom: 2px;
            width: 25px;
            background-color: #2c3e50;
            z-index: 2;
            box-shadow: 2px 0 5px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            align-items: center;
        }

        .stitch {
            width: 70%; height: 2px;
            background-color: rgba(255,255,255,0.7);
            box-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }

        .book-img {
            display: block;
            width: 100%;
            height: 240px;
            object-fit: cover;
            padding-left: 25px; /* 留出書脊位置 */
            box-sizing: border-box;
            background: #fff;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.4);
            filter: sepia(0.2);
            transition: filter 0.3s;
        }

        .shelf-cell:hover .book-wrapper { transform: scale(1.05); }
        .shelf-cell:hover .book-img { filter: sepia(0); }

        /* 書名標籤 */
        .book-tag {
            margin-top: 15px;
            background: #fdf5e6;
            color: #3e2723;
            padding: 5px 12px;
            font-size: 1rem;
            border-radius: 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            font-family: 'Noto Serif TC', serif;
            letter-spacing: 1px;
            border: 1px solid #d7ccc8;
            font-weight: 700;
        }

        /* =========================================
           4. 閱讀器 & 動畫
           ========================================= */
        .animating-cover {
            position: fixed;
            z-index: 9999;
            pointer-events: none;
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .reader-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(30, 20, 15, 0.95);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }
        .reader-overlay.active { opacity: 1; pointer-events: auto; }

        .reader-stage {
            position: relative;
            display: flex;
            align-items: center;
        }

        .reader-img {
            max-height: 85vh;
            max-width: 90vw;
            box-shadow: 0 0 30px rgba(0,0,0,0.8);
            border: 2px solid #5d4037;
            opacity: 0; 
            transition: opacity 0.5s ease;
        }

        .nav-btn {
            position: fixed;
            top: 50%;
            width: 60px; height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: #fff;
            display: flex;
            align-items: center; justify-content: center;
            font-size: 1.5rem; cursor: pointer;
            transition: 0.3s;
            border: 1px solid #8d6e63;
        }
        .nav-btn:hover { background: #8d6e63; }
        .prev-btn { left: 30px; }
        .next-btn { right: 30px; }

        .close-btn {
            position: absolute;
            top: 30px; right: 30px;
            color: #d7ccc8;
            font-size: 2rem; cursor: pointer;
            transition: 0.3s;
        }
        .close-btn:hover { color: #fff; transform: rotate(90deg); }

        .page-indicator {
            position: absolute; bottom: 20px;
            color: #a1887f;
            font-family: 'Noto Serif TC', serif;
            letter-spacing: 2px;
        }

        @media (max-width: 768px) {
            .shelf-container { padding: 15px; grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .shelf-cell { height: 260px; padding: 10px; }
            .book-wrapper { max-width: 100%; }
            .book-img { height: 180px; }
            h1 { font-size: 2rem; }
            .nav-btn { width: 40px; height: 40px; }
        }
    </style>
</head>
<body>

<header>
    <div class="plaque">
        <h1>江南星今次仲唔死?</h1>
    </div>
    <div class="subtitle">AI文言故事漫畫創作</div>
</header>

<div class="shelf-container">
    <?php foreach ($library as $index => $book): ?>
        <div class="shelf-cell" onclick="openBook(this, '<?php echo $book['cover']; ?>', <?php echo htmlspecialchars(json_encode($book['pages'])); ?>)">
            <div class="book-wrapper">
                <div class="book-spine-deco">
                    <div class="stitch"></div>
                    <div class="stitch"></div>
                    <div class="stitch"></div>
                    <div class="stitch"></div>
                </div>
                <img src="<?php echo $book['cover']; ?>" class="book-img" alt="Cover">
            </div>
            <div class="book-tag"><?php echo $book['title']; ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="reader-overlay" id="readerOverlay">
    <div class="close-btn" onclick="closeBook()">✕</div>
    
    <div class="nav-btn prev-btn" onclick="changePage(-1)">❮</div>
    <div class="reader-stage">
        <img src="" class="reader-img" id="readerImg">
    </div>
    <div class="nav-btn next-btn" onclick="changePage(1)">❯</div>
    
    <div class="page-indicator" id="pageIndicator"></div>
</div>

<script>
    let currentPages = [];
    let currentIndex = 0;
    const overlay = document.getElementById('readerOverlay');
    const readerImg = document.getElementById('readerImg');
    const pageIndicator = document.getElementById('pageIndicator');

    function openBook(element, coverSrc, pages) {
        if(pages.length === 0) return;

        currentPages = pages;
        currentIndex = 0;

        const imgEl = element.querySelector('.book-img');
        const rect = imgEl.getBoundingClientRect();

        const clone = document.createElement('img');
        clone.src = coverSrc;
        clone.className = 'animating-cover';
        
        clone.style.top = rect.top + 'px';
        clone.style.left = rect.left + 'px';
        clone.style.width = rect.width + 'px';
        clone.style.height = rect.height + 'px';
        
        document.body.appendChild(clone);
        
        const targetH = window.innerHeight * 0.85;
        const ratio = rect.width / rect.height;
        const targetW = targetH * ratio;
        
        const targetTop = (window.innerHeight - targetH) / 2;
        const targetLeft = (window.innerWidth - targetW) / 2;

        requestAnimationFrame(() => {
            clone.style.top = targetTop + 'px';
            clone.style.left = targetLeft + 'px';
            clone.style.width = targetW + 'px';
            clone.style.height = targetH + 'px';
        });

        overlay.classList.add('active');

        setTimeout(() => {
            const firstPageSrc = currentPages[0];
            const tempImg = new Image();
            tempImg.src = firstPageSrc;
            
            tempImg.onload = () => {
                readerImg.src = firstPageSrc;
                readerImg.style.opacity = 1;
                clone.style.opacity = 0; 
                setTimeout(() => { if(clone.parentNode) clone.parentNode.removeChild(clone); }, 300);
            };
            updateControls();
        }, 600);
    }

    function closeBook() {
        overlay.classList.remove('active');
        readerImg.style.opacity = 0;
        setTimeout(() => { readerImg.src = ''; }, 400);
    }

    function changePage(dir) {
        const nextIndex = currentIndex + dir;
        if(nextIndex >= 0 && nextIndex < currentPages.length) {
            readerImg.style.opacity = 0;
            setTimeout(() => {
                currentIndex = nextIndex;
                readerImg.src = currentPages[currentIndex];
                readerImg.onload = () => { readerImg.style.opacity = 1; };
                updateControls();
            }, 300);
        }
    }

    function updateControls() {
        pageIndicator.innerText = `${currentIndex + 1} / ${currentPages.length}`;
        document.querySelector('.prev-btn').style.visibility = currentIndex === 0 ? 'hidden' : 'visible';
        document.querySelector('.next-btn').style.visibility = currentIndex === currentPages.length - 1 ? 'hidden' : 'visible';
    }

    document.addEventListener('keydown', (e) => {
        if(!overlay.classList.contains('active')) return;
        if(e.key === 'ArrowLeft') changePage(-1);
        if(e.key === 'ArrowRight') changePage(1);
        if(e.key === 'Escape') closeBook();
    });
</script>

</body>
</html>