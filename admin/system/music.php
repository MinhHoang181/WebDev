<?php
    // Tao moi hoac mo session da ton tai
    session_start();
    // Ket noi database
    require_once("database.php");

    // PHP them bai hat vao database
    //
    // lay thong tin tu form them bai hat
    if (isset($_POST["addMusicBtn"])) {
        $name = $_POST["music-name"];
        $singer = $_POST["music-singer"];
        $lyric = $_POST["music-lyric"];
        // kiem tra  va luu file anh bai hat
        if ($_FILES["music-image"]["name"] != "") {
            $image = $_FILES["music-image"]["name"];
            $temp_name = $_FILES["music-image"]["tmp_name"];
            $location = "../../images/music/" . $image;      
            if (!file_exists($location)) {
                if(!move_uploaded_file($temp_name, $location)){
                    $_SESSION["add_fail"] = "Lỗi upload ảnh, vui lòng thử lại";
                    header("Location: ../index.php?menu=addmusic");
                }
            }
        } else {
            $image = "music_placeholder.png";
        }
        // kiem tra va luu link bai hat
        $link = $_FILES["music-file"]["name"];
        $temp_name = $_FILES["music-file"]["tmp_name"];
        $location = "../../album/" . $link;      
        if (!file_exists($location)) {
            if(!move_uploaded_file($temp_name, $location)) {
                $_SESSION["add_fail"] = "Lỗi upload nhạc, vui lòng thử lại";
                header("Location: ../index.php?menu=addmusic");
            }
        }
        // Cau lenh SQL luu vao database
        $sql = "INSERT INTO music (name, link, lyric, singer, image)
                VALUES ('$name', '$link', '$lyric', '$singer', '$image')";
        // luu vao database, neu loi thi thong bao va chuyen ve trang tao bai hat
        if ($conn->query($sql) === FALSE) {
            $_SESSION["add_fail"] = "Lỗi không thể thêm bài hát, vui lòng thử lại ";
        } else {
            $id_music = mysqli_insert_id($conn);
            $categories = GetAllCategory();
            while ($category = mysqli_fetch_assoc($categories)) {
                if (isset($_POST[$category["id"]])) {
                    $id_category = $category["id"];
                    $sql = "INSERT INTO categories_music (id_music, id_category) 
                            VALUES ('$id_music', '$id_category')";
                    if ($conn->query($sql) == FALSE) {
                        $_SESSION["add_fail"] = "Lỗi không thể thêm thể loại, vui lòng cập nhật lại";
                        header("Location: ../index.php?menu=addmusic");
                    }
                }
            }
            $_SESSION["add_success"] = "Thêm bài hát thành công";
        }
        header("Location: ../index.php?menu=addmusic");
    }

    // PHP cap nhat lai bai hat
    //
    // lay thong tin tu form cap nhat bai hat
    if (isset($_POST["updateBtn"])) {
        $id = $_GET["idMusic"];
        $music = GetMusicById($id);
        $name = $_POST["music-name"];
        $singer = $_POST["music-singer"];
        $lyric = $_POST["music-lyric"];

        // file anh bai hat
        if ($_FILES["music-image"]["name"] != "") {
            $image = $_FILES["music-image"]["name"];
            $temp_name = $_FILES["music-image"]["tmp_name"];
            $location = "../../images/music/" . $image;      
            if (!file_exists($location)) {
                if(!move_uploaded_file($temp_name, $location)){
                    $_SESSION["edit_fail"] = "Lỗi upload ảnh, vui lòng thử lại";
                    header("Location: ../index.php?menu=editmusic&id=".$id);
                }
            }
        } else {
            $image = $music["image"];
        }
        // file mp3 bai hat
        if ($_FILES["music-file"]["name"] != "") {
            $link = $_FILES["music-file"]["name"];
            $temp_name = $_FILES["music-file"]["tmp_name"];
            $location = "../../album/" . $link;      
            if (!file_exists($location)) {
                if(!move_uploaded_file($temp_name, $location)) {
                    $_SESSION["edit_fail"] = "Lỗi upload nhạc, vui lòng thử lại";
                    header("Location: ../index.php?menu=editmusic&id=".$id);
                }
            }
        } else {
            $link = $music["link"];
        }
        // Cau lenh SQL cap nhat vao database
        $sql = "UPDATE music
                SET name='$name', link='$link', lyric='$lyric', singer='$singer', image='$image'
                WHERE id='$id'";
        // ve lai trang cap nhat va thong bao ket qua
        if ($conn->query($sql) == FALSE) {
            $_SESSION["edit_fail"] = "Lỗi cập nhật, vui lòng thử lại";
        } else {
            // xoa cac the loai cua bai hat truoc cap nhat
            $sql = "DELETE FROM categories_music WHERE id_music='$id'";
            if ($conn->query($sql) == FALSE) {
                $_SESSION["edit_fail"] = "Lỗi xoá thể loại cũ, vui lòng cập nhật lại";
                header("Location: ../index.php?menu=editmusic&id=".$id);
            } else { 
                $categories = GetAllCategory();
                while ($category = mysqli_fetch_assoc($categories)) {
                    if (isset($_POST[$category["id"]])) {
                        $id_category = $category["id"];
                        $sql = "INSERT INTO categories_music (id_music, id_category) 
                                VALUES ('$id', '$id_category')";
                        if ($conn->query($sql) == FALSE) {
                            $_SESSION["edit_fail"] = "Lỗi không cập nhật thể loại, vui lòng cập nhật lại";
                            header("Location: ../index.php?menu=editmusic&id=".$id);
                        }
                    }
                }
            }
            $_SESSION["edit_success"] = "Cập nhật thành công";
        }
        header("Location: ../index.php?menu=editmusic&id=".$id);
    }

    // PHP Xoa bai hat khoi database
    //
    // lay id tu $_GET gui toi
    if (isset($_GET["removeMusic"]) && $_SESSION["user"]["user_type"] == "admin") {
        $id = $_GET["removeMusic"];
        // xoa cac the loai cua bai hat
        $sql = "DELETE FROM categories_music WHERE id_music='$id'";
        if ($conn->query($sql) == FALSE) {
            $_SESSION["delete_music_error"] = "Lỗi xoá thể loại cũ, vui lòng xoá lại";
            header("Location: ../index.php?menu=musicdata");
        } else { 
            $sql = "DELETE FROM music WHERE id='$id'";
            if ($conn->query($sql) == FALSE) {
                $_SESSION["delete_music_error"] = "Xoá bị lỗi, vui lòng thử lại";
            } 
            header("Location: ../index.php?menu=musicdata");
        }
    }

    // PHP Tao the loai moi
    //
    // lay thong tin tu form tao the loai
    if (isset($_POST["createCategoryBtn"])) {
        $id = $_POST["idCategory"];
        $name = $_POST["nameCategory"];
        // cau lenh SQL luu vao database
        $sql = "INSERT INTO categories (id, name)
                VALUES ('$id', '$name')";
        if ($conn->query($sql) == FALSE) {
            $_SESSION["category_fail"] = "Lỗi tạo thể loại, vui lòng thử lại" ;
        } else {
            $_SESSION["category_success"] = "Thêm thể loại thành công" ;
        }
        header("Location: ../index.php?menu=topicdata");
    }

    // PHP sua the loai
    //
    // lay thong tin tu form sua the loai
    if (isset($_POST["editCategoryBtn"])) {
        $id = $_POST["idCategory"];
        $name = $_POST["nameCategory"];
        // Cau lenh SQL cap nhat vao database
        $sql = "UPDATE categories
                SET name='$name'
                WHERE id='$id'";
        if ($conn->query($sql) == FALSE) {
            $_SESSION["category_fail"] = "Lỗi sửa thể loại, vui lòng thử lại" ;
        } else {
            $_SESSION["category_success"] = "Sửa thể loại thành công" ;
        }
        header("Location: ../index.php?menu=topicdata");
    }

    // PHP Xoa the loai
    //
    // lay id tu $_GET gui toi
    if (isset($_GET["removeCategory"]) && $_SESSION["user"]["user_type"] == "admin") { 
        $id = $_GET["removeCategory"];
        // Xoa cac the loai trong cac bai hat duoc luu
        $sql = "DELETE FROM categories_music WHERE id_category='$id'";
        if ($conn->query($sql) == FALSE) {
            $_SESSION["category_fail"] = "Lỗi xoá thể loại cũ, vui lòng xoá lại";
            header("Location: ../index.php?menu=musicdata");
        } else {
            $sql = "DELETE FROM categories WHERE id='$id'";
            if ($conn->query($sql) == FALSE) {
                $_SESSION["category_fail"] = "Lỗi xoá thể loại, vui lòng xoá lại";
            }
            header("Location: ../index.php?menu=topicdata");
        }
    }
?>