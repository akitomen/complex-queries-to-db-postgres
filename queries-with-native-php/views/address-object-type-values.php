<table class="table">
	<thead>
	<tr>
		<th scope="col">Id</th>
		<th scope="col">Address Object Type Id</th>
		<th scope="col">Value</th>
	</tr>
	</thead>
	<tbody>
    <?php
    foreach ($types as $type) { ?>
		<tr>
			<th scope="row"><?= $type['id'] ?></th>
			<td><?= $type['aot_id'] ?></td>
			<td><?= $type['value'] ?></td>
		</tr>
    <?php
    } ?>
	</tbody>
</table>
