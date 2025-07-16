<?php
require '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');

    if ($id > 0 && $question && $answer) {
        try {
            $stmt = $conn->prepare("UPDATE faq SET question = :question, answer = :answer WHERE id = :id");
            $stmt->execute([
                ':id' => $id,
                ':question' => $question,
                ':answer' => $answer
            ]);
            header("Location: ../faq?success=Faq updated successfully"); // Redirect as needed
            exit;
        } catch (PDOException $e) {
            error_log("FAQ Update Error: " . $e->getMessage());
            echo "Database error.";
        }
    } else {
        echo "All fields are required.";
    }
}
?>
