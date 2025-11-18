<?php
if (!isset($isEdit)) {
    $isEdit = false;
}
if (!isset($formTitle)) {
    $formTitle = $isEdit ? "Edit occupation" : "Create occupation";
}
if (!isset($submitLabel)) {
    $submitLabel = $isEdit ? "Update occupation" : "Save occupation";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($formTitle); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        form {
            max-width: 600px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
            margin-top: 2px;
        }

        .checkbox-group,
        .zombie-group {
            margin-top: 5px;
        }

        .checkbox-group label,
        .zombie-group label {
            font-weight: normal;
            display: inline-block;
            margin-right: 10px;
        }

        .errors {
            background: #ffe0e0;
            color: #e25c5cff;
            border: 1px solid #e74040ff;
            padding: 10px;
            margin-bottom: 15px;
        }

        .field-error {
            color: #e74040ff;
            font-size: 14px;
            margin-top: 5px;
            font-weight: bold;
        }

        .field-wrapper {
            margin-bottom: 15px;
        }

        .field-wrapper.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .actions {
            margin-top: 15px;
        }

        .actions a {
            margin-left: 10px;
        }
    </style>
    <script>
        function updateFormOnTypeChange() {
            var form = document.getElementById('occupation_form');
            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'update_type';
            hiddenInput.value = '1';
            form.appendChild(hiddenInput);
            form.submit();
        }
    </script>
</head>

<body>
<h1><?php echo htmlspecialchars($formTitle); ?></h1>
<?php if (!empty($errors['general'])): ?>
    <div class="general-errors">
        <ul>
            <?php foreach ($errors['general'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" id="occupation_form">
    <?php if ($isEdit && isset($occupationId)): ?>
        <input type="hidden" name="occupation_id" value="<?php echo (int)$occupationId; ?>">
    <?php endif; ?>

    <label for="post_id">Post</label>
    <select name="post_id" id="post_id">
        <option value="">Select a post</option>
        <?php foreach ($posts as $post): ?>
            <option value="<?php echo $post['id_posts']; ?>"
                <?php echo ($postId == $post['id_posts']) ? 'selected' : ''; ?>>
                <?php echo $post['id_posts'] . ' - ' . htmlspecialchars($post['location']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['post_id'])): ?>
        <div class="field-error">
            <?php foreach ($errors['post_id'] as $msg): ?>
                <div><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <label for="occupation_type">Occupation type</label>
    <select name="occupation_type" id="occupation_type" onchange="updateFormOnTypeChange()">
        <option value=""> Select occupation type </option>
        <option value="WLF" <?php echo $occupationType === 'WLF' ? 'selected' : ''; ?>>WLF</option>
        <option value="SERAPHITES" <?php echo $occupationType === 'SERAPHITES' ? 'selected' : ''; ?>>Seraphites</option>
        <option value="INFECTED_NEST" <?php echo $occupationType === 'INFECTED_NEST' ? 'selected' : ''; ?>>Infected nest</option>
    </select>
    <?php if (!empty($errors['occupation_type'])): ?>
        <div class="field-error">
            <?php foreach ($errors['occupation_type'] as $msg): ?>
                <div><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <div class="field-wrapper <?php echo ($occupationType !== 'WLF') ? 'disabled' : ''; ?>">
        <label for="character_name">WLF character <?php if ($occupationType === 'WLF'): ?><span style="color: red;">*</span><?php endif; ?></label>
        <select name="character_name" id="character_name" <?php echo ($occupationType !== 'WLF') ? 'disabled' : ''; ?>>
            <option value="">Select character</option>
            <?php foreach ($wlfCharacters as $char): ?>
                <option value="<?php echo htmlspecialchars($char); ?>"
                    <?php echo ($characterName === $char) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($char); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['character_name'])): ?>
            <div class="field-error">
                <?php foreach ($errors['character_name'] as $msg): ?>
                    <div><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="field-wrapper <?php echo ($occupationType !== 'SERAPHITES') ? 'disabled' : ''; ?>">
        <label for="weapons">Seraphites weapons (comma separated) <?php if ($occupationType === 'SERAPHITES'): ?><span style="color: red;">*</span><?php endif; ?></label>
        <input type="text" name="weapons" id="weapons"
               value="<?php echo htmlspecialchars($weaponsText); ?>"
               <?php echo ($occupationType !== 'SERAPHITES') ? 'disabled' : ''; ?>>
        <?php if (!empty($errors['weapons'])): ?>
            <div class="field-error">
                <?php foreach ($errors['weapons'] as $msg): ?>
                    <div><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="field-wrapper <?php echo ($occupationType !== 'INFECTED_NEST') ? 'disabled' : ''; ?>">
        <label>Infected nest <?php if ($occupationType === 'INFECTED_NEST'): ?><span style="color: red;">*</span><?php endif; ?></label>
        <div class="zombie-group">
            <?php foreach ($zombieTypeOptions as $value => $label): ?>
                <label>
                    <input type="checkbox" name="zombie_types[]" value="<?php echo $value; ?>"
                        <?php echo in_array($value, $selectedZombies) ? 'checked' : ''; ?>
                        <?php echo ($occupationType !== 'INFECTED_NEST') ? 'disabled' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($errors['zombie_types'])): ?>
            <div class="field-error">
                <?php foreach ($errors['zombie_types'] as $msg): ?>
                    <div><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <label for="observation">Observation</label>
    <textarea name="observation" id="observation" rows="3"><?php
        echo htmlspecialchars($observation);
    ?></textarea>

    <div class="actions">
        <button type="submit" name="save"><?php echo htmlspecialchars($submitLabel); ?></button>
        <a href="index.php">Back to list</a>
    </div>
</form>
</body>

</html>