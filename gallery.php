<?php
/**
 * BonusBoss Portfolio Website - Galeri Sayfası
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Config dosyasını dahil et
require_once 'includes/config.php';

// Sayfa değişkenleri
$page_title = 'Galeri';
$current_page = 'gallery';

// Verileri al
$photos = get_gallery_photos();
$videos = get_gallery_videos();
$categories = get_categories('gallery');

// Breadcrumb oluştur
$breadcrumb = generate_breadcrumb($page_title);

// Header dahil et
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumb as $item): ?>
                        <li class="breadcrumb-item <?php echo isset($item['active']) ? 'active' : ''; ?>">
                            <?php if (isset($item['active'])): ?>
                            <?php echo escape_output($item['title']); ?>
                            <?php else: ?>
                            <a href="<?php echo escape_output($item['url']); ?>"><?php echo escape_output($item['title']); ?></a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                
                <h1 class="page-title"><?php echo escape_output(get_site_text('gallery_page_title', 'Galeri')); ?></h1>
                <p class="page-subtitle"><?php echo escape_output(get_site_text('gallery_page_subtitle', 'Fotoğraf ve Video Galerim')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Tabs -->
<section class="section section-light">
    <div class="container">
        <div class="gallery-tabs" data-aos="fade-up">
            <ul class="nav nav-pills justify-content-center" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos" type="button" role="tab">
                        <i class="fas fa-images"></i>
                        <?php echo escape_output(get_site_text('gallery_photos_title', 'Fotoğraflar')); ?>
                        <span class="badge"><?php echo count($photos); ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="videos-tab" data-bs-toggle="pill" data-bs-target="#videos" type="button" role="tab">
                        <i class="fas fa-video"></i>
                        <?php echo escape_output(get_site_text('gallery_videos_title', 'Videolar')); ?>
                        <span class="badge"><?php echo count($videos); ?></span>
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="tab-content">
            <!-- Photos Tab -->
            <div class="tab-pane fade show active" id="photos" role="tabpanel">
                <?php if (!empty($categories)): ?>
                <div class="gallery-filters" data-aos="fade-up">
                    <button class="gallery-filter active" data-filter="all">Tümü</button>
                    <?php foreach ($categories as $category): ?>
                    <button class="gallery-filter" data-filter="<?php echo $category['id']; ?>">
                        <?php echo escape_output($category['name']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="gallery-grid" data-aos="fade-up">
                    <?php foreach ($photos as $photo): ?>
                    <div class="gallery-item" data-category="<?php echo $photo['category_id']; ?>">
                        <div class="gallery-card">
                            <img src="assets/uploads/gallery/<?php echo escape_output($photo['image']); ?>" 
                                 alt="<?php echo escape_output($photo['title']); ?>" 
                                 class="gallery-image">
                            
                            <div class="gallery-overlay">
                                <div class="gallery-content">
                                    <h5><?php echo escape_output($photo['title']); ?></h5>
                                    <?php if ($photo['description']): ?>
                                    <p><?php echo escape_output($photo['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="gallery-actions">
                                        <a href="assets/uploads/gallery/<?php echo escape_output($photo['image']); ?>" 
                                           class="gallery-link" 
                                           data-lightbox="gallery"
                                           data-title="<?php echo escape_output($photo['title']); ?>">
                                            <i class="fas fa-search-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($photos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Henüz fotoğraf eklenmemiş</h4>
                    <p class="text-muted">Galeri fotoğraflarımız yakında eklenecek.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Videos Tab -->
            <div class="tab-pane fade" id="videos" role="tabpanel">
                <div class="videos-grid" data-aos="fade-up">
                    <?php foreach ($videos as $video): ?>
                    <div class="video-item">
                        <div class="video-card">
                            <div class="video-wrapper">
                                <?php if ($video['video_type'] == 'youtube'): ?>
                                    <?php 
                                    $video_id = '';
                                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video['video_url'], $match)) {
                                        $video_id = $match[1];
                                    }
                                    ?>
                                    <div class="video-thumbnail" data-video-id="<?php echo $video_id; ?>">
                                        <img src="https://img.youtube.com/vi/<?php echo $video_id; ?>/maxresdefault.jpg" 
                                             alt="<?php echo escape_output($video['title']); ?>" 
                                             class="video-image">
                                        <div class="video-play-btn">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                <?php elseif ($video['video_type'] == 'vimeo'): ?>
                                    <?php 
                                    $video_id = '';
                                    if (preg_match('/vimeo\.com\/(\d+)/', $video['video_url'], $match)) {
                                        $video_id = $match[1];
                                    }
                                    ?>
                                    <div class="video-thumbnail" data-video-type="vimeo" data-video-id="<?php echo $video_id; ?>">
                                        <img src="https://vumbnail.com/<?php echo $video_id; ?>.jpg" 
                                             alt="<?php echo escape_output($video['title']); ?>" 
                                             class="video-image">
                                        <div class="video-play-btn">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <video controls class="video-player">
                                        <source src="<?php echo escape_output($video['video_url']); ?>" type="video/mp4">
                                        Tarayıcınız video oynatmayı desteklemiyor.
                                    </video>
                                <?php endif; ?>
                            </div>
                            
                            <div class="video-content">
                                <h5><?php echo escape_output($video['title']); ?></h5>
                                <?php if ($video['description']): ?>
                                <p><?php echo escape_output($video['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($video['duration']): ?>
                                <div class="video-duration">
                                    <i class="fas fa-clock"></i>
                                    <?php echo escape_output($video['duration']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($videos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-video text-muted" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Henüz video eklenmemiş</h4>
                    <p class="text-muted">Video galerimiz yakında eklenecek.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Stats -->
<section class="section section-dark">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Galeri İstatistikleri</h2>
            <p>Görsel içeriklerimizin sayısal verileri</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-number"><?php echo count($photos); ?></div>
                <div class="stat-label">Fotoğraf</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-number"><?php echo count($videos); ?></div>
                <div class="stat-label">Video</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-number"><?php echo count($categories); ?></div>
                <div class="stat-label">Kategori</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-number"><?php echo count($photos) + count($videos); ?></div>
                <div class="stat-label">Toplam İçerik</div>
            </div>
        </div>
    </div>
</section>

<!-- Instagram Feed -->
<section class="section section-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Instagram Akışımız</h2>
            <p>Son Instagram gönderilerimizden örnekler</p>
        </div>
        
        <div class="instagram-feed" data-aos="fade-up">
            <div class="row">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="instagram-item">
                        <img src="assets/images/instagram/instagram-<?php echo $i; ?>.jpg" 
                             alt="Instagram Post <?php echo $i; ?>" 
                             class="instagram-image">
                        <div class="instagram-overlay">
                            <div class="instagram-stats">
                                <div class="stat">
                                    <i class="fas fa-heart"></i>
                                    <span><?php echo rand(100, 999); ?></span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-comment"></i>
                                    <span><?php echo rand(10, 99); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?php echo escape_output(get_setting('social_instagram')); ?>" 
                   class="btn btn-outline btn-lg" 
                   target="_blank" 
                   rel="noopener noreferrer">
                    <i class="fab fa-instagram"></i>
                    Instagram'da Takip Et
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section section-dark">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="text-gradient">Sosyal Medyada Takip Et</h2>
                <p class="lead mb-4">Güncel içeriklerim ve projelerim hakkında bilgi sahibi olmak için sosyal medya hesaplarımı takip edin.</p>
                
                <div class="social-links">
                    <?php if (get_setting('social_instagram')): ?>
                    <a href="<?php echo escape_output(get_setting('social_instagram')); ?>" 
                       class="social-link" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <i class="fab fa-instagram"></i>
                        Instagram
                    </a>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_tiktok')): ?>
                    <a href="<?php echo escape_output(get_setting('social_tiktok')); ?>" 
                       class="social-link" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <i class="fab fa-tiktok"></i>
                        TikTok
                    </a>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_youtube')): ?>
                    <a href="<?php echo escape_output(get_setting('social_youtube')); ?>" 
                       class="social-link" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <i class="fab fa-youtube"></i>
                        YouTube
                    </a>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_telegram')): ?>
                    <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" 
                       class="social-link" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <i class="fab fa-telegram"></i>
                        Telegram
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="video-container">
                    <!-- Video will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery filtering
    const galleryFilters = document.querySelectorAll('.gallery-filter');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            
            // Update active filter
            galleryFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter gallery items
            galleryItems.forEach(item => {
                if (filterValue === 'all' || item.dataset.category === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Video thumbnail click
    const videoThumbnails = document.querySelectorAll('.video-thumbnail');
    const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
    
    videoThumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const videoId = this.dataset.videoId;
            const videoType = this.dataset.videoType || 'youtube';
            const container = document.querySelector('#videoModal .video-container');
            
            let embedUrl = '';
            if (videoType === 'youtube') {
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            } else if (videoType === 'vimeo') {
                embedUrl = `https://player.vimeo.com/video/${videoId}`;
            }
            
            container.innerHTML = `
                <iframe src="${embedUrl}" 
                        width="100%" 
                        height="400" 
                        frameborder="0" 
                        allowfullscreen>
                </iframe>
            `;
            
            videoModal.show();
        });
    });
    
    // Clear video when modal is closed
    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function() {
        document.querySelector('#videoModal .video-container').innerHTML = '';
    });
});
</script>

<?php include 'includes/footer.php'; ?>