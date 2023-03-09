<?php

function encrypt_password($password, $key) {
    // Enkripsi password menggunakan metode AES-256-CBC
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("AES-256-CBC"));
    $encrypted_password = openssl_encrypt($password, "AES-256-CBC", $key, 0, $iv);
    
    // Gabungkan IV dengan password terenkripsi
    $encrypted_password_with_iv = base64_encode($iv . $encrypted_password);
    
    return $encrypted_password_with_iv;
}

function decrypt_password($encrypted_password_with_iv, $key) {
    // Ambil IV dari password terenkripsi
    $encrypted_password_with_iv = base64_decode($encrypted_password_with_iv);
    $iv_length = openssl_cipher_iv_length("AES-256-CBC");
    $iv = substr($encrypted_password_with_iv, 0, $iv_length);
    $encrypted_password = substr($encrypted_password_with_iv, $iv_length);
    
    // Deskripsi password terenkripsi menggunakan metode AES-256-CBC
    $password = openssl_decrypt($encrypted_password, "AES-256-CBC", $key, 0, $iv);
    
    return $password;
}

// Baca password untuk enkripsi
$key = readline("Masukkan password enkripsi: ");

// Baca daftar password dari file terenkripsi
if (file_exists("passwords.enc")) {
    $encrypted_passwords = file_get_contents("passwords.enc");
    $passwords = unserialize($encrypted_passwords);
} else {
    $passwords = array();
}

while (true) {
    // Tampilkan menu pilihan
    echo "Pilihan:\n";
    echo "1. Buat password baru\n";
    echo "2. Lihat daftar password\n";
    echo "3. Hapus password\n";
    echo "4. Keluar\n";
    
    // Baca pilihan dari pengguna
    $choice = readline("Masukkan pilihan: ");
    
    switch ($choice) {
        case "1":
            // Baca label dan panjang password baru dari pengguna
            $label = readline("Masukkan label password: ");
            $length = readline("Masukkan panjang password (minimal 8 karakter): ");
            
            if ($length < 8) {
                echo "Panjang password minimal 8 karakter!\n";
            } else {
                // Buat password acak dengan panjang yang ditentukan
                $password = bin2hex(random_bytes($length / 2));
                
                // Enkripsi password
                $encrypted_password = encrypt_password($password, $key);
                
                // Tambahkan password terenkripsi ke dalam daftar password
                $passwords[$label] = $encrypted_password;
                
                // Tampilkan password yang dibuat
                echo "Password baru untuk label '$label': $password\n";
            }
            break;
            
        case "2":
            // Tampilkan daftar label password
            echo "Daftar password:\n";
            foreach ($passwords as $label => $encrypted_password) {
                // Deskripsi password terenkripsi
                $password = decrypt_password($encrypted_password, $key);
                // Tampilkan label dan password
                echo "$label: $password\n";
            }
            break;
            
        case "3":
            // Baca label password yang akan dihapus
            $label = readline("Label password yang akan dihapus: ");
            
                        // Cek apakah label ada dalam daftar
            if (array_key_exists($label, $passwords)) {
                // Hapus password terenkripsi dari daftar password
                unset($passwords[$label]);
                echo "Password dengan label '$label' berhasil dihapus.\n";
            } else {
                echo "Label password tidak ditemukan dalam daftar.\n";
            }
            break;
            
        case "4":
            // Simpan daftar password terenkripsi ke dalam file
            $encrypted_passwords = serialize($passwords);
            file_put_contents("passwords.enc", $encrypted_passwords);
            
            echo "Terima kasih!\n";
            exit(0);
            
        default:
            echo "Pilihan tidak valid!\n";
            break;
    }
}
?>

