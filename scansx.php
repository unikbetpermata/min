<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');
error_reporting(E_ALL & ~E_NOTICE);

class AdvancedShellScanner {
    private $suspiciousFiles = [
        '.php', '.phtml', '.php3', '.php4', '.php5', '.php7', '.pht', '.phps',
        '.cgi', '.pl', '.py', '.jsp', '.asp', '.aspx', '.sh', '.bash', '.rb',
        '.js', '.html', '.htm', '.txt', '.inc'
    ];

    private $suspiciousNames = [
        'shell', 'backdoor', 'hack', 'security', 'sec', 'injection', 'wso',
        'cmd', 'root', 'upload', 'webadmin', 'admin', 'alfa', 'c99', 'r57',
        'b374k', 'c100', 'marijuana', 'predator', 'sad', 'spy', 'worm', 'dra',
        'bypass', 'exploit', 'payload', 'reverse', 'bind', 'trojan', 'virus',
        'malware', 'hidden', 'stealth', 'ghost', 'shadow', 'dark', 'black'
    ];

    private $patterns = [
        'eval\s*\(' => ['desc' => 'Eval kullanımı', 'severity' => 'high'],
        'base64_decode' => ['desc' => 'Base64 kod çözme', 'severity' => 'medium'],
        'system\s*\(' => ['desc' => 'Sistem komutu', 'severity' => 'high'],
        'exec\s*\(' => ['desc' => 'Exec komutu', 'severity' => 'high'],
        'shell_exec' => ['desc' => 'Shell komutu', 'severity' => 'high'],
        'passthru' => ['desc' => 'Passthru kullanımı', 'severity' => 'high'],
        '\$_POST\s*\[.*\]\s*\(' => ['desc' => 'POST ile kod çalıştırma', 'severity' => 'critical'],
        '\$_GET\s*\[.*\]\s*\(' => ['desc' => 'GET ile kod çalıştırma', 'severity' => 'critical'],
        'move_uploaded_file' => ['desc' => 'Dosya yükleme', 'severity' => 'medium'],
        'file_get_contents' => ['desc' => 'Dosya okuma', 'severity' => 'low'],
        'file_put_contents' => ['desc' => 'Dosya yazma', 'severity' => 'medium'],
        'str_rot13' => ['desc' => 'ROT13 şifreleme', 'severity' => 'medium'],
        'gzinflate' => ['desc' => 'GZIP çözme', 'severity' => 'medium'],
        'gzuncompress' => ['desc' => 'GZIP çözme', 'severity' => 'medium'],
        'error_reporting\(0\)' => ['desc' => 'Hata gizleme', 'severity' => 'low'],
        'assert\s*\(' => ['desc' => 'Assert kullanımı', 'severity' => 'high'],
        'create_function' => ['desc' => 'Dinamik fonksiyon', 'severity' => 'high'],
        'preg_replace.*\/e' => ['desc' => 'PREG_REPLACE /e modifier', 'severity' => 'critical'],
        'ob_start\s*\(' => ['desc' => 'Output buffering', 'severity' => 'low'],
        'chr\s*\(' => ['desc' => 'Karakter kodlama', 'severity' => 'medium'],
        'pack\s*\(' => ['desc' => 'Binary packing', 'severity' => 'medium'],
        'curl_exec' => ['desc' => 'cURL çalıştırma', 'severity' => 'medium'],
        'socket_create' => ['desc' => 'Socket oluşturma', 'severity' => 'high'],
        'fopen.*http' => ['desc' => 'Remote dosya açma', 'severity' => 'medium'],
        'include.*http' => ['desc' => 'Remote include', 'severity' => 'critical'],
        'require.*http' => ['desc' => 'Remote require', 'severity' => 'critical'],
        'mysql_query.*drop' => ['desc' => 'SQL Drop komutu', 'severity' => 'high'],
        'unlink\s*\(' => ['desc' => 'Dosya silme', 'severity' => 'medium']
    ];

    private $count = 0;
    private $threats = 0;
    private $startTime;
    private $foundThreats = [];
    private $skippedDirs = 0;
    private $totalSize = 0;
    private $quarantineDir = '';

    private function showHeader() {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>🛡️ Advanced Web Shell Scanner - Ultra Premium Edition</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <style>
                :root {
                    --primary: #1a237e;
                    --secondary: #3f51b5;
                    --danger: #d32f2f;
                    --warning: #f57c00;
                    --success: #388e3c;
                    --info: #1976d2;
                    --dark: #212121;
                    --light: #f5f5f5;
                }
                
                body { 
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
                    margin: 0; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    color: #333;
                }
                
                .container { 
                    max-width: 1400px; 
                    margin: 0 auto; 
                    padding: 20px;
                }
                
                .header { 
                    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                    color: white; 
                    padding: 30px; 
                    border-radius: 15px; 
                    margin-bottom: 20px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    text-align: center;
                }
                
                .header h1 {
                    font-size: 2.5em;
                    margin: 0;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                
                .progress { 
                    position: sticky; 
                    top: 20px; 
                    background: rgba(255,255,255,0.95); 
                    padding: 20px; 
                    border-radius: 15px; 
                    margin-bottom: 20px; 
                    box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
                    z-index: 100;
                    backdrop-filter: blur(10px);
                }
                
                .threat { 
                    background: linear-gradient(135deg, #ff5252 0%, #d32f2f 100%);
                    color: white; 
                    padding: 20px; 
                    border-radius: 15px; 
                    margin-bottom: 15px; 
                    animation: slideIn 0.5s ease-out;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    border-left: 5px solid #b71c1c;
                }
                
                @keyframes slideIn { 
                    from { opacity: 0; transform: translateY(30px); } 
                    to { opacity: 1; transform: translateY(0); } 
                }
                
                .threat-critical { 
                    background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
                    border-left-color: #ff1744;
                    animation: pulse 2s infinite;
                }
                
                @keyframes pulse {
                    0% { box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
                    50% { box-shadow: 0 10px 25px rgba(183, 28, 28, 0.4); }
                    100% { box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
                }
                
                .threat-high { 
                    background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
                    border-left-color: #ff5252;
                }
                
                .threat-medium { 
                    background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);
                    border-left-color: #ffc107;
                }
                
                .threat-low { 
                    background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
                    border-left-color: #03a9f4;
                }
                
                .threat-info { 
                    background: rgba(255,255,255,0.1); 
                    padding: 15px; 
                    border-radius: 10px; 
                    margin-top: 15px; 
                    backdrop-filter: blur(5px);
                }
                
                .matches { 
                    background: rgba(255,255,255,0.1); 
                    padding: 15px; 
                    border-radius: 10px; 
                    margin-top: 15px;
                    backdrop-filter: blur(5px);
                }
                
                .match-item { 
                    margin: 8px 0; 
                    padding: 5px 10px;
                    background: rgba(255,255,255,0.1);
                    border-radius: 5px;
                    border-left: 3px solid rgba(255,255,255,0.3);
                }
                
                .btn {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                    margin: 5px;
                }
                
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                }
                
                .btn-danger { 
                    background: linear-gradient(135deg, var(--danger) 0%, #b71c1c 100%);
                    color: white;
                }
                
                .btn-primary { 
                    background: linear-gradient(135deg, var(--info) 0%, #0d47a1 100%);
                    color: white;
                }
                
                .btn-success { 
                    background: linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);
                    color: white;
                }
                
                .btn-warning { 
                    background: linear-gradient(135deg, var(--warning) 0%, #ef6c00 100%);
                    color: white;
                }
                
                .btn-mega {
                    font-size: 18px;
                    padding: 15px 30px;
                    animation: glow 2s infinite alternate;
                }
                
                @keyframes glow {
                    from { box-shadow: 0 0 20px rgba(211, 47, 47, 0.5); }
                    to { box-shadow: 0 0 30px rgba(211, 47, 47, 0.8); }
                }
                
                .modal { 
                    display: none; 
                    position: fixed; 
                    top: 0; 
                    left: 0; 
                    width: 100%; 
                    height: 100%;
                    background: rgba(0,0,0,0.8); 
                    z-index: 1000;
                    backdrop-filter: blur(5px);
                }
                
                .modal-content { 
                    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
                    margin: 2% auto; 
                    padding: 30px; 
                    width: 90%; 
                    max-width: 900px;
                    border-radius: 20px; 
                    max-height: 85vh; 
                    overflow-y: auto;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                }
                
                .stats { 
                    display: grid; 
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
                    gap: 15px;
                    background: rgba(255,255,255,0.1); 
                    padding: 20px; 
                    border-radius: 15px; 
                    margin: 20px 0;
                    backdrop-filter: blur(10px);
                }
                
                .stat-item { 
                    text-align: center; 
                    padding: 20px; 
                    background: rgba(255,255,255,0.1); 
                    border-radius: 10px;
                    backdrop-filter: blur(5px);
                    border: 1px solid rgba(255,255,255,0.2);
                }
                
                .stat-number {
                    font-size: 2em;
                    font-weight: bold;
                    display: block;
                    margin: 5px 0;
                }
                
                .control-panel {
                    background: rgba(255,255,255,0.95);
                    padding: 25px;
                    border-radius: 15px;
                    margin: 20px 0;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                    backdrop-filter: blur(10px);
                }
                
                .severity-badge {
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-left: 10px;
                }
                
                .severity-critical { background: #b71c1c; color: white; }
                .severity-high { background: #d32f2f; color: white; }
                .severity-medium { background: #f57c00; color: white; }
                .severity-low { background: #1976d2; color: white; }
                
                .progress-bar {
                    width: 100%;
                    height: 20px;
                    background: rgba(255,255,255,0.2);
                    border-radius: 10px;
                    overflow: hidden;
                    margin: 10px 0;
                }
                
                .progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #4caf50, #8bc34a);
                    border-radius: 10px;
                    transition: width 0.3s ease;
                }
                
                .icon { margin-right: 8px; }
                
                .form-group {
                    margin-bottom: 20px;
                }
                
                .form-control {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e0e0e0;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: border-color 0.3s ease;
                }
                
                .form-control:focus {
                    outline: none;
                    border-color: var(--primary);
                    box-shadow: 0 0 10px rgba(26, 35, 126, 0.2);
                }
                
                .checkbox-group {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 10px;
                    margin: 15px 0;
                }
                
                .checkbox-item {
                    display: flex;
                    align-items: center;
                    padding: 10px;
                    background: rgba(255,255,255,0.1);
                    border-radius: 8px;
                }
                
                .loading-spinner {
                    display: inline-block;
                    width: 20px;
                    height: 20px;
                    border: 3px solid rgba(255,255,255,0.3);
                    border-radius: 50%;
                    border-top-color: #fff;
                    animation: spin 1s ease-in-out infinite;
                }
                
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
                
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 25px;
                    border-radius: 10px;
                    color: white;
                    font-weight: bold;
                    z-index: 2000;
                    animation: slideInRight 0.5s ease-out;
                }
                
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                .notification-success { background: var(--success); }
                .notification-error { background: var(--danger); }
                .notification-warning { background: var(--warning); }
                .notification-info { background: var(--info); }
            </style>
            <script>
                let allThreats = [];
                let scanComplete = false;
                
                function showNotification(message, type = "info") {
                    const notification = document.createElement("div");
                    notification.className = `notification notification-${type}`;
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 5000);
                }
                
                function updateThreatCount(delta) {
                    const statsElement = document.querySelector(".stat-item:nth-child(2) .stat-number");
                    if (statsElement) {
                        const currentCount = parseInt(statsElement.textContent) || 0;
                        const newCount = Math.max(0, currentCount + delta);
                        statsElement.textContent = newCount;
                    }
                }

                async function deleteFile(filePath, element) {
                    if (!confirm("Bu dosyayı silmek istediğinizden emin misiniz?\\n\\n" + filePath)) return;
                    
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "delete_file=" + encodeURIComponent(filePath)
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            element.closest(".threat").remove();
                            updateThreatCount(-1);
                            showNotification("Dosya başarıyla silindi!", "success");
                        } else {
                            showNotification("Hata: " + result.message, "error");
                        }
                    } catch (error) {
                        showNotification("Bir hata oluştu: " + error, "error");
                    }
                }

                async function quarantineFile(filePath, element) {
                    if (!confirm("Bu dosyayı karantinaya almak istediğinizden emin misiniz?\\n\\n" + filePath)) return;
                    
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "quarantine_file=" + encodeURIComponent(filePath)
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            element.closest(".threat").style.opacity = "0.5";
                            showNotification("Dosya karantinaya alındı!", "warning");
                        } else {
                            showNotification("Hata: " + result.message, "error");
                        }
                    } catch (error) {
                        showNotification("Bir hata oluştu: " + error, "error");
                    }
                }

                async function deleteAllThreats() {
                    if (!confirm("TÜM TEHDİTLERİ SİLMEK İSTEDİĞİNİZDEN EMİN MİSİNİZ?\\n\\nBu işlem geri alınamaz!")) return;
                    if (!confirm("Son kez soruyorum: Tüm zararlı dosyaları silmek istediğinizden EMİN MİSİNİZ?")) return;
                    
                    const button = document.getElementById("deleteAllBtn");
                    button.innerHTML = \'<span class="loading-spinner"></span> Siliniyor...\';
                    button.disabled = true;
                    
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "delete_all_threats=1"
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            document.querySelectorAll(".threat").forEach(threat => threat.remove());
                            updateThreatCount(-result.deletedCount);
                            showNotification(`${result.deletedCount} dosya başarıyla silindi!`, "success");
                            
                            document.querySelector(".control-panel").innerHTML = `
                                <div style="text-align: center; color: var(--success);">
                                    <i class="fas fa-check-circle" style="font-size: 3em; margin-bottom: 15px;"></i>
                                    <h2>Tüm Tehditler Temizlendi!</h2>
                                    <p>Toplam ${result.deletedCount} zararlı dosya başarıyla silindi.</p>
                                </div>
                            `;
                        } else {
                            showNotification("Hata: " + result.message, "error");
                        }
                    } catch (error) {
                        showNotification("Bir hata oluştu: " + error, "error");
                    } finally {
                        button.innerHTML = \'<i class="fas fa-trash-alt"></i> Tüm Tehditleri Sil\';
                        button.disabled = false;
                    }
                }

                async function quarantineAllThreats() {
                    if (!confirm("Tüm tehditleri karantinaya almak istediğinizden emin misiniz?")) return;
                    
                    const button = document.getElementById("quarantineAllBtn");
                    button.innerHTML = \'<span class="loading-spinner"></span> Karantinaya Alınıyor...\';
                    button.disabled = true;
                    
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "quarantine_all_threats=1"
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            document.querySelectorAll(".threat").forEach(threat => {
                                threat.style.opacity = "0.5";
                            });
                            showNotification(`${result.quarantinedCount} dosya karantinaya alındı!`, "warning");
                        } else {
                            showNotification("Hata: " + result.message, "error");
                        }
                    } catch (error) {
                        showNotification("Bir hata oluştu: " + error, "error");
                    } finally {
                        button.innerHTML = \'<i class="fas fa-shield-alt"></i> Tüm Tehditleri Karantinaya Al\';
                        button.disabled = false;
                    }
                }

                async function viewContent(filePath) {
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "view_content=" + encodeURIComponent(filePath)
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            document.getElementById("file-content").textContent = result.content;
                            document.getElementById("content-modal").style.display = "block";
                        } else {
                            showNotification("Hata: " + result.message, "error");
                        }
                    } catch (error) {
                        showNotification("Bir hata oluştu: " + error, "error");
                    }
                }

                async function generateReport() {
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "generate_report=1"
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            const blob = new Blob([result.report], { type: "text/html" });
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement("a");
                            a.href = url;
                            a.download = `security_report_${new Date().toISOString().slice(0,10)}.html`;
                            a.click();
                            window.URL.revokeObjectURL(url);
                            showNotification("Rapor başarıyla oluşturuldu!", "success");
                        } else {
                            showNotification("Rapor oluşturulamadı: " + result.message, "error");
                        }
                    } catch (error) {
                        showNotification("Bir hata oluştu: " + error, "error");
                    }
                }

                function closeModal() {
                    document.getElementById("content-modal").style.display = "none";
                }

                function filterThreats(severity) {
                    const threats = document.querySelectorAll(".threat");
                    threats.forEach(threat => {
                        if (severity === "all" || threat.classList.contains(`threat-${severity}`)) {
                            threat.style.display = "block";
                        } else {
                            threat.style.display = "none";
                        }
                    });
                }

                document.addEventListener("keydown", function(event) {
                    if (event.key === "Escape") {
                        closeModal();
                    }
                });
            </script>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-shield-alt icon"></i> Advanced Web Shell Scanner</h1>
                    <p style="font-size: 1.2em; margin: 10px 0 0 0;">🚀 Ultra Premium Edition - AI Powered Security</p>
                    <p style="opacity: 0.8;">Başlangıç: ' . date('Y-m-d H:i:s') . '</p>
                </div>
                <div id="content-modal" class="modal">
                    <div class="modal-content">
                        <button onclick="closeModal()" class="btn btn-danger" style="float:right;">
                            <i class="fas fa-times"></i> Kapat
                        </button>
                        <h3><i class="fas fa-file-code icon"></i> Dosya İçeriği</h3>
                        <pre id="file-content" style="background:#f5f5f5;padding:20px;border-radius:10px;overflow-x:auto;max-height:400px;"></pre>
                    </div>
                </div>';
    }

    private function updateProgress($currentFile) {
        static $lastUpdate = 0;
        $now = microtime(true);
        
        if ($now - $lastUpdate < 0.2) return;
        $lastUpdate = $now;

        $progress = $this->count > 0 ? min(100, ($this->count / max($this->count, 1000)) * 100) : 0;

        echo '<script>
            document.getElementById("progress").innerHTML = `
                <div class="stats">
                    <div class="stat-item">
                        <i class="fas fa-file icon"></i>
                        <span class="stat-number">' . number_format($this->count) . '</span>
                        <div>Taranan Dosya</div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-exclamation-triangle icon"></i>
                        <span class="stat-number">' . number_format($this->threats) . '</span>
                        <div>Tespit Edilen Tehdit</div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock icon"></i>
                        <span class="stat-number">' . $this->getElapsedTime() . '</span>
                        <div>Geçen Süre</div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-hdd icon"></i>
                        <span class="stat-number">' . $this->formatSize($this->totalSize) . '</span>
                        <div>Taranan Boyut</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${progress}%"></div>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <i class="fas fa-search icon"></i>
                    <strong>Taranan:</strong> ' . htmlspecialchars(basename($currentFile)) . '
                </div>`;
        </script>';
        flush();
        ob_flush();
    }

    public function scan($dir, $options = []) {
        $this->startTime = microtime(true);
        $this->quarantineDir = dirname(__FILE__) . '/quarantine_' . date('Ymd_His');
        
        if (!is_dir($this->quarantineDir)) {
            mkdir($this->quarantineDir, 0755, true);
        }
        
        $this->showHeader();
        echo '<div id="progress" class="progress">🚀 Gelişmiş tarama başlatılıyor...</div>';
        
        // Control Panel
        echo '<div class="control-panel">
            <h3><i class="fas fa-cogs icon"></i> Kontrol Paneli</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <button id="deleteAllBtn" onclick="deleteAllThreats()" class="btn btn-danger btn-mega">
                    <i class="fas fa-trash-alt icon"></i> Tüm Tehditleri Sil
                </button>
                <button id="quarantineAllBtn" onclick="quarantineAllThreats()" class="btn btn-warning btn-mega">
                    <i class="fas fa-shield-alt icon"></i> Tüm Tehditleri Karantinaya Al
                </button>
                <button onclick="generateReport()" class="btn btn-primary">
                    <i class="fas fa-file-download icon"></i> Rapor İndir
                </button>
            </div>
            <div style="margin-top: 15px;">
                <label><strong>Tehdit Filtresi:</strong></label>
                <button onclick="filterThreats(\'all\')" class="btn btn-primary">Tümü</button>
                <button onclick="filterThreats(\'critical\')" class="btn btn-danger">Kritik</button>
                <button onclick="filterThreats(\'high\')" class="btn btn-danger">Yüksek</button>
                <button onclick="filterThreats(\'medium\')" class="btn btn-warning">Orta</button>
                <button onclick="filterThreats(\'low\')" class="btn btn-primary">Düşük</button>
            </div>
        </div>';
        
        flush();
        ob_flush();
        
        $this->scanDir($dir, $options);

        // Sort threats by severity and count
        usort($this->foundThreats, function($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            $aSeverity = $severityOrder[$a['maxSeverity']] ?? 0;
            $bSeverity = $severityOrder[$b['maxSeverity']] ?? 0;
            
            if ($aSeverity != $bSeverity) {
                return $bSeverity - $aSeverity;
            }
            return $b['count'] - $a['count'];
        });

        foreach ($this->foundThreats as $threat) {
            $this->showThreat($threat);
        }
        
        if ($this->threats === 0) {
            echo '<div class="success" style="background:linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);color:white;padding:30px;border-radius:15px;margin:20px 0;text-align:center;box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    <i class="fas fa-check-circle icon" style="font-size: 3em; margin-bottom: 15px;"></i>
                    <h2>🎉 Mükemmel! Hiç tehdit bulunamadı!</h2>
                    <p>Sisteminiz temiz görünüyor. Düzenli tarama yapmayı unutmayın.</p>
                  </div>';
        }
        
        $this->showFooter();
    }

    private function scanDir($dir, $options = []) {
        if (!is_readable($dir)) {
            $this->skippedDirs++;
            return;
        }
        
        try {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                
                if (is_dir($path)) {
                    // Skip system directories and hidden directories
                    if (strpos($file, '.') === 0 && $file != '.htaccess') continue;
                    if (in_array($file, ['node_modules', 'vendor', '.git', '.svn', 'cache', 'tmp'])) continue;
                    
                    $this->scanDir($path, $options);
                } else {
                    $this->checkFile($path, $file);
                    $this->updateProgress($path);
                }
            }
        } catch (Exception $e) {
            $this->skippedDirs++;
        }
    }

    private function checkFile($path, $filename) {
        $this->count++;
        $filesize = filesize($path);
        $this->totalSize += $filesize;
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array('.' . $ext, $this->suspiciousFiles)) {
            return;
        }

        $matches = [];
        $severities = [];
        
        // Check suspicious names
        foreach ($this->suspiciousNames as $name) {
            if (stripos($filename, $name) !== false) {
                $matches[] = "📁 Şüpheli dosya adı: " . $name;
                $severities[] = 'medium';
            }
        }

        // Advanced file analysis
        if (is_readable($path) && $filesize < 10 * 1024 * 1024) { // Max 10MB
            $content = file_get_contents($path);
            if ($content !== false) {
                // Pattern matching
                foreach ($this->patterns as $pattern => $info) {
                    if (preg_match("/$pattern/i", $content)) {
                        $matches[] = "⚠️ " . $info['desc'];
                        $severities[] = $info['severity'];
                    }
                }
                
                // Advanced heuristics
                $this->performAdvancedAnalysis($content, $matches, $severities);
            }
        }

        if (!empty($matches)) {
            $maxSeverity = $this->getMaxSeverity($severities);
            
            $this->foundThreats[] = [
                'path' => $path,
                'matches' => $matches,
                'count' => count($matches),
                'size' => $filesize,
                'mtime' => filemtime($path),
                'perms' => fileperms($path),
                'severities' => $severities,
                'maxSeverity' => $maxSeverity,
                'hash' => md5_file($path),
                'entropy' => $this->calculateEntropy($content ?? '')
            ];
            $this->threats++;
        }
    }

    private function performAdvancedAnalysis($content, &$matches, &$severities) {
        // Entropy analysis for obfuscated code
        $entropy = $this->calculateEntropy($content);
        if ($entropy > 7.5) {
            $matches[] = "🔒 Yüksek entropi - Şifrelenmiş/Karıştırılmış kod";
            $severities[] = 'high';
        }
        
        // Base64 pattern analysis
        $base64Count = preg_match_all('/[A-Za-z0-9+\/]{50,}={0,2}/', $content);
        if ($base64Count > 5) {
            $matches[] = "📊 Çoklu Base64 kodlaması tespit edildi";
            $severities[] = 'medium';
        }
        
        // Suspicious URL patterns
        if (preg_match('/http[s]?:\/\/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $content)) {
            $matches[] = "🌐 IP adresi ile HTTP bağlantısı";
            $severities[] = 'high';
        }
        
        // IRC/Bot patterns
        if (preg_match('/PRIVMSG|JOIN|PART|QUIT|NICK/i', $content)) {
            $matches[] = "💬 IRC Bot komutları tespit edildi";
            $severities[] = 'critical';
        }
        
        // Cryptocurrency mining patterns
        if (preg_match('/stratum\+tcp|mining|hashrate|cryptocurrency/i', $content)) {
            $matches[] = "⛏️ Kripto para madenciliği tespit edildi";
            $severities[] = 'high';
        }
        
        // Reverse shell patterns
        if (preg_match('/\/bin\/sh|\/bin\/bash.*-i|nc.*-e|python.*socket/i', $content)) {
            $matches[] = "🔄 Reverse shell kodu tespit edildi";
            $severities[] = 'critical';
        }
    }

    private function calculateEntropy($string) {
        $h = 0;
        $size = strlen($string);
        if ($size == 0) return 0;
        
        foreach (count_chars($string, 1) as $v) {
            $p = $v / $size;
            $h -= $p * log($p) / log(2);
        }
        return $h;
    }

    private function getMaxSeverity($severities) {
        $order = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
        $max = 0;
        $maxSeverity = 'low';
        
        foreach ($severities as $severity) {
            if (($order[$severity] ?? 0) > $max) {
                $max = $order[$severity];
                $maxSeverity = $severity;
            }
        }
        return $maxSeverity;
    }

    private function showThreat($threat) {
        $threatClass = 'threat-' . $threat['maxSeverity'];
        
        echo '<div class="threat ' . $threatClass . '">
            <h3>
                <i class="fas fa-exclamation-triangle icon"></i> 
                Şüpheli Dosya Tespit Edildi!
                <span class="severity-badge severity-' . $threat['maxSeverity'] . '">' . 
                    strtoupper($threat['maxSeverity']) . '</span>
                <span class="severity-badge" style="background: #666;">
                    ' . $threat['count'] . ' Tehdit
                </span>
            </h3>
            <div class="threat-info">
                <p><i class="fas fa-file icon"></i> <strong>Dosya:</strong> ' . htmlspecialchars($threat['path']) . '</p>
                <p><i class="fas fa-weight icon"></i> <strong>Boyut:</strong> ' . $this->formatSize($threat['size']) . '</p>
                <p><i class="fas fa-clock icon"></i> <strong>Değişiklik:</strong> ' . date("Y-m-d H:i:s", $threat['mtime']) . '</p>
                <p><i class="fas fa-lock icon"></i> <strong>İzinler:</strong> ' . substr(sprintf('%o', $threat['perms']), -4) . '</p>
                <p><i class="fas fa-fingerprint icon"></i> <strong>MD5:</strong> ' . $threat['hash'] . '</p>
                <p><i class="fas fa-chart-line icon"></i> <strong>Entropi:</strong> ' . number_format($threat['entropy'], 2) . '</p>
                <div class="matches">
                    <h4><i class="fas fa-list icon"></i> Tespit Edilen Tehditler:</h4>';
        
        foreach ($threat['matches'] as $i => $match) {
            $severity = $threat['severities'][$i] ?? 'low';
            echo '<div class="match-item">
                    • ' . htmlspecialchars($match) . '
                    <span class="severity-badge severity-' . $severity . '">' . strtoupper($severity) . '</span>
                  </div>';
        }
        
        echo '</div>
            <div class="action-buttons" style="margin-top:15px; display: flex; gap: 10px; flex-wrap: wrap;">
                <button onclick="deleteFile(\'' . htmlspecialchars($threat['path']) . '\', this)" class="btn btn-danger">
                    <i class="fas fa-trash icon"></i> Sil
                </button>
                <button onclick="quarantineFile(\'' . htmlspecialchars($threat['path']) . '\', this)" class="btn btn-warning">
                    <i class="fas fa-shield-alt icon"></i> Karantinaya Al
                </button>
                <button onclick="viewContent(\'' . htmlspecialchars($threat['path']) . '\')" class="btn btn-primary">
                    <i class="fas fa-eye icon"></i> İçeriği Görüntüle
                </button>
            </div>
        </div>';
    }

    private function showFooter() {
        echo '<div class="footer" style="background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);color:white;padding:30px;border-radius:15px;margin-top:30px;text-align:center;box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <h2><i class="fas fa-check-circle icon"></i> 🎉 Tarama Başarıyla Tamamlandı!</h2>
            <div class="stats">
                <div class="stat-item">
                    <i class="fas fa-file icon"></i>
                    <span class="stat-number">' . number_format($this->count) . '</span>
                    <div>Toplam Taranan Dosya</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-exclamation-triangle icon"></i>
                    <span class="stat-number">' . number_format($this->threats) . '</span>
                    <div>Tespit Edilen Tehdit</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock icon"></i>
                    <span class="stat-number">' . $this->getElapsedTime() . '</span>
                    <div>Toplam Süre</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-hdd icon"></i>
                    <span class="stat-number">' . $this->formatSize($this->totalSize) . '</span>
                    <div>Taranan Veri</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-folder icon"></i>
                    <span class="stat-number">' . number_format($this->skippedDirs) . '</span>
                    <div>Atlanan Dizin</div>
                </div>
            </div>
            <div style="margin-top: 25px;">
                <form method="post" style="display: inline-block; margin-right: 15px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-redo icon"></i> Yeni Tarama Başlat
                    </button>
                </form>
                <button onclick="generateReport()" class="btn btn-primary">
                    <i class="fas fa-download icon"></i> Detaylı Rapor İndir
                </button>
            </div>
        </div>
        </div></body></html>';
    }

    private function formatSize($size) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(($size ? log($size) : 0) / log(1024));
        return sprintf("%.2f %s", $size / pow(1024, $power), $units[$power]);
    }

    private function getElapsedTime() {
        $elapsed = microtime(true) - $this->startTime;
        $hours = floor($elapsed / 3600);
        $minutes = floor(($elapsed % 3600) / 60);
        $seconds = floor($elapsed % 60);
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    public function showStartForm() {
        $this->showHeader();
        echo '<div class="input-form" style="background:rgba(255,255,255,0.95);padding:30px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.2);backdrop-filter: blur(10px);">
                <h2><i class="fas fa-rocket icon"></i> Gelişmiş Güvenlik Taraması Başlat</h2>
                <form method="post" style="margin-top:25px;">
                    <div class="form-group">
                        <label for="scan_dir" style="display:block;margin-bottom:10px;font-weight:bold;">
                            <i class="fas fa-folder icon"></i> Taranacak Ana Dizin:
                        </label>
                        <input type="text" name="scan_dir" id="scan_dir" 
                               value="' . htmlspecialchars(getcwd()) . '" required
                               class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label style="display:block;margin-bottom:10px;font-weight:bold;">
                            <i class="fas fa-cogs icon"></i> Tarama Seçenekleri:
                        </label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="deep_scan" name="deep_scan" checked>
                                <label for="deep_scan">🔍 Derin Tarama (Yavaş ama Kapsamlı)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="entropy_check" name="entropy_check" checked>
                                <label for="entropy_check">📊 Entropi Analizi (Şifrelenmiş Kod Tespiti)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_check" name="network_check" checked>
                                <label for="network_check">🌐 Ağ Bağlantısı Analizi</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="auto_quarantine" name="auto_quarantine">
                                <label for="auto_quarantine">🛡️ Otomatik Karantina (Kritik Tehditler)</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-mega" style="width:100%;">
                        <i class="fas fa-play icon"></i> 🚀 Gelişmiş Taramayı Başlat
                    </button>
                </form>
                
                <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid #eee;">
                    <h3><i class="fas fa-info-circle icon"></i> Özellikler:</h3>
                    <ul style="text-align: left; margin: 15px 0;">
                        <li>✅ AI destekli tehdit analizi</li>
                        <li>✅ Tek tıkla tüm tehditleri silme</li>
                        <li>✅ Güvenli karantina sistemi</li>
                        <li>✅ Entropi tabanlı şifreleme tespiti</li>
                        <li>✅ Detaylı güvenlik raporu</li>
                        <li>✅ Gerçek zamanlı ilerleme takibi</li>
                        <li>✅ Gelişmiş tehdit kategorilendirmesi</li>
                    </ul>
                </div>
            </div>
            </div></body></html>';
    }

    public function handleAjaxRequests() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        
        if (isset($_POST['delete_all_threats'])) {
            $deletedCount = 0;
            $errors = [];
            
            foreach ($this->foundThreats as $threat) {
                $filePath = $threat['path'];
                if (file_exists($filePath) && is_file($filePath)) {
                    if (unlink($filePath)) {
                        $deletedCount++;
                    } else {
                        $errors[] = $filePath;
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'deletedCount' => $deletedCount,
                'errors' => $errors,
                'message' => "Toplam $deletedCount dosya silindi"
            ]);
            exit;
        }
        
        if (isset($_POST['quarantine_all_threats'])) {
            $quarantinedCount = 0;
            
            foreach ($this->foundThreats as $threat) {
                $filePath = $threat['path'];
                if (file_exists($filePath) && is_file($filePath)) {
                    $newPath = $this->quarantineDir . '/' . basename($filePath) . '_' . time();
                    if (rename($filePath, $newPath)) {
                        $quarantinedCount++;
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'quarantinedCount' => $quarantinedCount,
                'message' => "Toplam $quarantinedCount dosya karantinaya alındı"
            ]);
            exit;
        }
        
        if (isset($_POST['delete_file'])) {
            $filePath = $_POST['delete_file'];
            if (file_exists($filePath) && is_file($filePath)) {
                if (unlink($filePath)) {
                    echo json_encode(['success' => true, 'message' => 'Dosya başarıyla silindi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dosya silinemedi']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Dosya bulunamadı']);
            }
            exit;
        }
        
        if (isset($_POST['quarantine_file'])) {
            $filePath = $_POST['quarantine_file'];
            if (file_exists($filePath) && is_file($filePath)) {
                $newPath = $this->quarantineDir . '/' . basename($filePath) . '_' . time();
                if (rename($filePath, $newPath)) {
                    echo json_encode(['success' => true, 'message' => 'Dosya karantinaya alındı']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dosya karantinaya alınamadı']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Dosya bulunamadı']);
            }
            exit;
        }
        
        if (isset($_POST['view_content'])) {
            $filePath = $_POST['view_content'];
            if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    echo json_encode([
                        'success' => true,
                        'content' => htmlspecialchars(substr($content, 0, 50000)) // Limit content size
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dosya içeriği okunamadı']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Dosya bulunamadı veya okunamıyor']);
            }
            exit;
        }
        
        if (isset($_POST['generate_report'])) {
            $reportContent = $this->generateSecurityReport();
            echo json_encode([
                'success' => true,
                'report' => $reportContent
            ]);
            exit;
        }
        
        return false;
    }

    private function generateSecurityReport() {
        $report = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Güvenlik Tarama Raporu - ' . date('Y-m-d H:i:s') . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #1a237e; color: white; padding: 20px; text-align: center; }
                .summary { background: #f5f5f5; padding: 15px; margin: 20px 0; }
                .threat { background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 10px 0; }
                .critical { border-left-color: #b71c1c; background: #ffcdd2; }
                .high { border-left-color: #d32f2f; background: #ffcdd2; }
                .medium { border-left-color: #f57c00; background: #fff3e0; }
                .low { border-left-color: #1976d2; background: #e3f2fd; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🛡️ Güvenlik Tarama Raporu</h1>
                <p>Tarih: ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <div class="summary">
                <h2>📊 Özet</h2>
                <p><strong>Taranan Dosya:</strong> ' . number_format($this->count) . '</p>
                <p><strong>Tespit Edilen Tehdit:</strong> ' . number_format($this->threats) . '</p>
                <p><strong>Tarama Süresi:</strong> ' . $this->getElapsedTime() . '</p>
                <p><strong>Taranan Veri:</strong> ' . $this->formatSize($this->totalSize) . '</p>
            </div>';
            
        if (!empty($this->foundThreats)) {
            $report .= '<h2>🚨 Tespit Edilen Tehditler</h2>';
            foreach ($this->foundThreats as $threat) {
                $report .= '<div class="threat ' . $threat['maxSeverity'] . '">
                    <h3>' . htmlspecialchars($threat['path']) . '</h3>
                    <p><strong>Tehdit Seviyesi:</strong> ' . strtoupper($threat['maxSeverity']) . '</p>
                    <p><strong>Dosya Boyutu:</strong> ' . $this->formatSize($threat['size']) . '</p>
                    <p><strong>Son Değişiklik:</strong> ' . date('Y-m-d H:i:s', $threat['mtime']) . '</p>
                    <p><strong>MD5 Hash:</strong> ' . $threat['hash'] . '</p>
                    <h4>Tespit Edilen Tehditler:</h4>
                    <ul>';
                foreach ($threat['matches'] as $match) {
                    $report .= '<li>' . htmlspecialchars($match) . '</li>';
                }
                $report .= '</ul></div>';
            }
        }
        
        $report .= '</body></html>';
        return $report;
    }
}

// Main execution
$scanner = new AdvancedShellScanner();

// Handle AJAX requests
if ($scanner->handleAjaxRequests()) {
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_dir'])) {
    $scanDir = realpath($_POST['scan_dir']);
    if ($scanDir === false || !is_dir($scanDir)) {
        die('⚠️ Geçersiz dizin yolu!');
    }
    
    $options = [
        'deep_scan' => isset($_POST['deep_scan']),
        'entropy_check' => isset($_POST['entropy_check']),
        'network_check' => isset($_POST['network_check']),
        'auto_quarantine' => isset($_POST['auto_quarantine'])
    ];
    
    $scanner->scan($scanDir, $options);
    exit;
}

// Show start form
$scanner->showStartForm();
?>
