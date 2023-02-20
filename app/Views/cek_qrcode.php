<html>
	<head>
		<meta charset="utf-8">
		<link rel="icon" href="<?= base_url('assets/tut.png') ?>">
		<title>Cek QRODE</title>
		<style type="text/css">
			.th{
				border: 1px solid #b6bab7;
			    padding: 4px 10px;
			}
		</style>
	</head>
	<body>
		

		<div style="margin-left: 100px; margin-right: 100px; margin-top: 20px;">

			<?php if ($status): ?>
				<div style="background-color: #36AE7C; padding: 20px; width: 100%; margin-bottom: 20px; text-align: center; color: #fff;" >
					<h3>DATA TERINDENTIFIKASI</h3>
				</div>
			<?php else: ?>
				<div style="background-color: #EB5353; padding: 20px; width: 100%; margin-bottom: 20px; text-align: center; color: #fff;" >
					<h3>DATA TIDAK ADA DI DATABASE!</h3>
				</div>
			<?php endif ?>

			<table>
				<tr>
					<td>NAMA</td>
					<td>: <span style="text-transform: capitalize;"><?= $mahasiswa['nama_lengkap'] ?></span></td>
				</tr>
				<tr>
					<td>STAMBUK</td>
					<td>: <?= $mahasiswa['nim'] ?></td>
				</tr>
				<tr>
					<td>PROGRAM STUDI</td>
					<td>: <span style="text-transform: capitalize;"><?= $mahasiswa['nama_prodi'] ?></span></td>
				</tr>
			</table>
			<br />

			<h3 style="margin-left: 8px;">Daftar Matakuliah : </h3>

			<div style="width: 100%;">
				<table style="width: 100%; border-collapse: collapse; color: #232323;">
					<tr style="background-color: #b6bab7;">
						<th class="th">No</th>
						<th class="th">Nama Matakuliah</th>
						<th class="th">SKS</th>
					</tr>
					<?php 
						$jmlSks = 0;
						foreach ($matakuliah as $key => $row): ?>
						<tr>
							<td class="th" class="th" align="center"><?= $key+1 ?></td>
							<td class="th" class="th"><?= $row['matakuliah'] ?></td>
							<td class="th" class="th" align="center"><?= $row['sks'] ?></td>
						</tr>
					<?php $jmlSks += (int)$row['sks']; endforeach ?>
					<tr>
						<th class="th" colspan="2" align="center">
							Jumlah
						</th>
						<th class="th" align="center"><?= $jmlSks ?></th>
					</tr>
				</table>
			</div>	

			<h3 style="margin-left: 8px;">Nilai Akhir : </h3>

			<table style="width: 100%; border-collapse: collapse; color: #232323;">
				<tr style="background-color: #b6bab7;">
					<th class="th" colspan="3">NILAI</th>
				</tr>
				<tr>
					<td class="th" align="center">Indikator penilaian</td>
					<td class="th" align="center">Angka</td>
					<td class="th" align="center">Grade</td>
				</tr>
				<tr>
					<td class="th">Pembekalan</td>
					<td class="th" align="center"><?= $nilai['n_pembekalan'] ?></td>
					<td class="th" rowspan="4" align="center">
						<h1 style="font-size: 35px"><?= $nilai['grade'] ?></h1>
					</td>
				</tr>
				<tr>
					<td class="th">Pelaksanaan</td>
					<td class="th" align="center"><?= $nilai['n_pelaksanaan'] ?></td>
				</tr>
				<tr>
					<td class="th">Laporan</td>
					<td class="th" align="center"><?= $nilai['n_laporan'] ?></td>
				</tr>
				<tr>
					<td class="th">Nilai Akhir</td>
					<td class="th" align="center"><?= $nilai['n_akhir'] ?></td>
				</tr>
			</table>

		</div>


	</body>
</html>