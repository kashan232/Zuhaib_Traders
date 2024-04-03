<!-- meta tags and other links -->
@include('admin_panel.include.header_include')

<body>
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        <!-- sidebar end -->

        <!-- navbar-wrapper start -->
        @include('admin_panel.include.navbar_include')
        <!-- navbar-wrapper end -->

        <div class="body-wrapper">
            <div class="bodywrapper__inner">

                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Units</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
                        <form action="" method="GET" class="d-flex gap-2">
                            <div class="input-group w-auto">
                                <input type="search" name="search" class="form-control bg--white"
                                    placeholder="Search..." value="">
                                <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
                            </div>

                        </form>
                        <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"
                            data-modal_title="Add New Unit">
                            <i class="las la-plus"></i>Add New </button>
                        <button type="button" class="btn btn-sm btn-outline--info importBtn">
                            <i class="las la-cloud-upload-alt"></i>Import CSV </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                <div class="table-responsive--sm table-responsive">
                                    <table class="table table--light">
                                        <thead>
                                            <tr>
                                                <th>S.N.</th>
                                                <th>Name</th>
                                                <th>Prodcuts</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($all_unit as $unit)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $unit->unit }}</td>
                                                <td>2</td>
                                                <td>
                                                    <div class="button--group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline--primary cuModalBtn"
                                                            data-resource="{&quot;id&quot;:9,&quot;name&quot;:&quot;bag&quot;,&quot;status&quot;:1,&quot;type&quot;:1,&quot;task_by&quot;:null,&quot;created_at&quot;:&quot;2022-10-22T10:13:17.000000Z&quot;,&quot;updated_at&quot;:&quot;2022-10-22T10:13:17.000000Z&quot;,&quot;products_count&quot;:2}"
                                                            data-modal_title="Update Unit" data-has_status="1">
                                                            <i class="la la-pencil"></i>Edit </button>
                                                        {{-- <button type="button"
                                                            class="btn btn-sm btn-outline-danger  disabled  confirmationBtn"
                                                            data-question="Are you sure to delete this unit?"
                                                            data-action="https://script.viserlab.com/torylab/admin/unit/delete/9">
                                                            <i class="la la-trash"></i>Delete </button> --}}
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table><!-- table end -->
                                </div>
                            </div>
                        </div><!-- card end -->
                    </div>
                </div>

                <!--Create & Update Modal -->
                <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><span class="type"></span> <span>Add Unit</span></h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="{{ route('store-unit') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="unit" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Import Unit</h4>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="la la-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <form method="post" action="https://script.viserlab.com/torylab/admin/unit/import"
                                id="importForm" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="zv105s8kd1s2nyZ6nvoqU6pROYAnsCPYkYXTDlWn">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <div class="alert alert-warning p-3" role="alert">
                                            <p>
                                                - Format your CSV the same way as the sample file below. <br>
                                                - Valid fields Tip: make sure name of fields must be following: name<br>
                                                - Required And Unique field's (name)<br>
                                                - When an error occurs download the error file and correct the incorrect
                                                cells and import that file again through format.<br>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="fw-bold">Select File</label>
                                        <input type="file" class="form-control" name="file" accept=".csv" required>
                                        <div class="mt-1">
                                            <small class="d-block">
                                                Supported files: <b class="fw-bold">csv</b>
                                            </small>
                                            <small>
                                                Download sample template file from here <a
                                                    href="https://script.viserlab.com/torylab/assets/files/sample/unit.csv"
                                                    title="Download csv file" class="text--primary" download>
                                                    <b>csv</b>
                                                </a>

                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="Submit" class="btn btn--primary w-100 h-45">Import</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmation Alert!</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="" method="POST">
                                {{-- <input type="hidden" name="_token" value="zv105s8kd1s2nyZ6nvoqU6pROYAnsCPYkYXTDlWn"> --}}
                                <div class="modal-body">
                                    <p class="question"></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">No</button>
                                    <button type="submit" class="btn btn--primary">Yes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
    </div>
    @include('admin_panel.include.footer_include')