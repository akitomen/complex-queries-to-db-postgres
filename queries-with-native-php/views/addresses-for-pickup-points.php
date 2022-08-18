<table class="table">
    <thead>
    <tr>
        <th scope="col">Id</th>
        <th scope="col">Address Type Id</th>
        <th scope="col">Full Address</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($addresses as $address) { ?>
        <tr>
            <th scope="row"><?= $address['id'] ?></th>
            <td><?= $address['atp_id'] ?></td>
            <td><?= $address['full_address'] ?></td>
        </tr>
        <?php
    } ?>
    </tbody>
</table>
