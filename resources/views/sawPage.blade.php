@extends('template')
@section('title', 'SAW')
@section('intro-header')
    <!-- Header -->
    <header class="intro-header text-black">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    </header>
    <!-- END : Header -->
@endsection

<!-- Main -->
<br><br><br><br>
<div class="container">
    <h1>SAW</h1>
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ request()->is('Kriteria') ? 'active': null }}" href="{{ url('Kriteria') }}"
               role="tab">Data Kriteria</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('Mahasiswa') ? 'active': null }}" href="{{ url('Mahasiswa') }}"
               role="tab">Data Mahasiswa</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('Penilaian') ? 'active': null }}" href="{{ url('Penilaian') }}"
               role="tab">Data Penilaian</a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link {{ request()->is('Skkm') ? 'active': null }}" href="{{ url('Skkm') }}"
               role="tab">SKKM</a>
        </li> -->
    </ul>


    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane {{ request()->is('Kriteria') ? 'active': null }}" href="{{ url('Kriteria') }}"
             role="tabpanel">
            <h3>Data Kriteria</h3>
            <table class="table table-borderless">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Kriteria</th>
                    <th>Nama Kriteria</th>
                    <th>Nilai Atribut</th>
                    <th>Bobot</th>
                    <th>Keterangan</th>
                    <th>Action</th>
                    <th>
                        <a href="{{ url('/Kriteria/route_tambah_krt_saw') }}">
                            <img class="img-fluid" alt="Responsive image" src="template_madan/images/iconplus.png">
                            Tambah Data
                        </a>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; ?>
                <?php
                if (is_array($vdata) || is_object($vdata) ){ ?>
                @foreach($vdata as $kriteria)
                    <tr>
                        <td><?php echo($i++); ?></td>
                        <td>{{$kriteria['kode']}}</td>
                        <td>{{$kriteria['nama']}}</td>
                        <td>{{$kriteria['atribut']}}</td>
                        <td>{{$kriteria['bobot']}}</td>
                        <td>{{$kriteria['keterangan']}}</td>
                        <td>
                            <a href="/Kriteria/edit_kriteria/{{ $kriteria['id'] }}">
                                <img style="width:10%; height: auto;" alt="Responsive image"
                                     src="template_madan/images/edit.png">
                            </a>
                            <a href="/Kriteria/hapus_kriteria/{{ $kriteria['id'] }}">
                                <img style="width:10%; height: auto;" src="template_madan/images/delete.png"
                                     alt="Responsive image">
                            </a>
                        </td>
                    </tr>
                @endforeach
                <?php } ?>
                </tbody>
            </table>
        </div>


        <div class="tab-pane {{ request()->is('Mahasiswa') ? 'active': null }}" href="{{ url('Mahasiswa') }}"
             role="tabpanel">
            <h3>Mahasiswa</h3>
            <table class="table">
                <th>No</th>
                <th>Nama</th>
                <th>Nilai IPK</th>
                <th>Nilai Prilaku</th>
                <tr>
                    <?php $no = 1; ?>
                    <?php if (is_array($krt) || is_object($krt)){ ?>
                    @foreach ($krt as $dt_mhs)
                        <td><?php echo($no++); ?></td>
                        <td>{{ $dt_mhs['nama'] }}</td>
                        <td>{{ $dt_mhs['IPK'] }}</td>
                        <td>{{ $dt_mhs['akumulasi_skor'] }}</td>
                </tr>
                @endforeach
                <?php } ?>
            </table>

        </div>


        <!-- Tab panes -->
        <div class="tab-pane {{ request()->is('Skkm') ? 'active': null }}" href="{{ url('Skkm') }}" role="tabpanel">
            <h3>Data SKKM</h3>
            <table class="table">
                <th>No</th>
                <th>Nama</th>
                <th>SKKM</th>
                <th>Aksi</th>
                <tr>
                    <?php $no = 1; ?>
                    <?php if (is_array($krt) || is_object($krt)){ ?>
                    @foreach ($krt as $dt_mhs)
                        <td><?php echo($no++); ?></td>
                        <td>{{ $dt_mhs['nama'] }}</td>
                        <td>{{ $dt_mhs['skkm'] }}</td>
                        <td class="text-center"><a href="{{ url('/Skkm/route_tambah_skkm') }}"
                                                   class="btn btn-sm btn-warning">Tambah</a></td>
                        <td class="text-center"><a href="#"
                                                   class="btn btn-sm btn-warning">Edit</a></td>
                </tr>
                @endforeach
                <?php } ?>
            </table>
        </div>


        <div class="tab-pane {{ request()->is('Penilaian') ? 'active': null }}" href="{{ url('Penilaian') }}"
             role="tabpanel">
            <h3>Normalisasi</h3>
            <table class="table">
                <th>No</th>
                <th>Nama</th>
                <th>Nilai IPK</th>
                <th>Nilai Perilaku</th>
                <th>Nilai</th>
                <th>SKKM</th>
                <th>Aksi</th>
                <tr>
                    <?php $no = 1; ?>
                    <?php if (is_array($krt) || is_object($krt)){ ?>
                    @foreach ($krt as $key => $value)
                        <td><?php echo($no++); ?></td>
                            <td>{{ $value['nama'] }}</td>

                            <td>
                                {{ $value['IPK']}}
                            </td>
                            <td>
                                {{ $value['akumulasi_skor'] }}
                            </td>
                        <td>
                            {{ $key }}
                        </td>
                        <td>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                            Tambah SKKM
                        </button>
                        </td>
                        <td>
                            <form method="POST">
                                @csrf
                                <a href="#"> class="btn btn-sm btn-info">Edit SKKM</a>
                            </form>
                        </td>
                        
                </tr>
                @endforeach
                <?php } ?>
            </table>
        </div>




<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form>
      <div class="modal-body">
    <form >
  <div class="form-group">
    <label >SKKM</label>
    <input type="skkm" class="form-control"  placeholder="Enter SKKM">
    </div>
  

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>



        <div class="tab-pane {{ request()->is('Perhitungan') ? 'active': null }}" href="{{ url('Perhitungan') }}"
             role="tabpanel">
            <h3>Kriteria</h3>
            <table class="table">
                <th>No</th>
                <th>Alternatif</th>
                <th>C01</th>
                <th>C02</th>
                <th>SKKM</th>
                <tr>
                    <td>sdasda</td>
                    <td>asd</td>
                    <td>asd</td>
                    <td>asda</td>
                    <td>asda</td>
                </tr>
            </table>
            <button type="button" name="button">Seleksi SKKM</button>
            <h4>Hasil Ranking</h4>
            <table class="table">
                <th>No</th>
                <th>Alternatif</th>
                <th>Ranking</th>
                <tr>
                    <td>sdasda</td>
                    <td>asd</td>
                    <td>asd</td>
                </tr>
            </table>
            <span onclick="this.parentElement.style.display='none'"></span>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
