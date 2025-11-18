<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AreaF5 - Test Técnico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #e4d0d0ff;
        }
    </style>
</head>

<body>
    <h1>Ellie's map :D</h1>
    <div class="actions">
        <form method="get" action="occupation_create.php">
            <button type="submit">Create new occupation</button>
        </form>
        <form method="get" action="export_csv.php">
            <button type="submit">Export CSV</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Puesto</th>
                <th>Localización</th>
                <th>Tipo de ocupación</th>
                <th>Nombre</th>
                <th>Armas</th>
                <th>Tipo de zombie</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($result)): ?>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['post_number']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['occupation_type']) ?></td>
                        <td><?= $row['name'] !== null ? htmlspecialchars($row['name']) : 'NULL' ?></td>
                        <td><?= $row['weapons'] !== null ? htmlspecialchars($row['weapons']) : 'NULL' ?></td>
                        <td><?= $row['zombie_types'] !== null ? htmlspecialchars($row['zombie_types']) : 'NULL' ?></td>
                        <td><?= $row['observation'] !== null && $row['observation'] !== '' ? htmlspecialchars($row['observation']) : 'NULL' ?></td>
                        <td>
                            <form method="post" action="occupation_delete.php"
                                onsubmit="return confirm('Do you really want to delete this occupation?');">
                                <input type="hidden" name="occupation_id"
                                    value="<?php echo (int)$row['occupation_id']; ?>">
                                <button type="submit" name="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">add something first uwu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>