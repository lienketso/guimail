<li>
    <div class="{{ isset($class) ? $class : '' }}">
        <div class="folder-title" style="display: flex; align-items: center; justify-content: space-between;">
            <span>
                <i class="fa fa-folder folder-icon"></i> {{ $folder->name }} 
                @if($folder->ngay_nop)
                    <span class="ngay-nop">Ngày nộp: {{ $folder->ngay_nop ? date('d/m/Y', strtotime($folder->ngay_nop)) : '' }}</span>
                @endif
            </span>
            <div>
                <button type="button" class="btn btn-sm  add-subfolder" data-parent-folder-id="{{ $folder->id }}" title="Thêm thư mục con">
                    <i class="fa fa-plus"></i>
                </button>
                <button type="button" class="btn btn-sm upload-file-btn" data-folder-id="{{ $folder->id }}" title="Upload file">
                    <i class="fa fa-cloud-upload"></i>
                </button>
                @if($folder->name && Str::contains(Str::lower($folder->name), 'lần'))
                     <button type="button" class="btn btn-sm btn-link add-ngay-nop" 
                        data-folder-id="{{ $folder->id }}"><i class="fa fa-calendar-plus"></i></button>
                @endif
            </div>
        </div>
        @if(count($folder->files))
            <ul class="file-list">
                @foreach($folder->files as $file)
                    <li>
                        <span class="file-title">
                            <i class="fa fa-file file-icon"></i>
                            <a class="file-link" href="{{ route('folders.download', $file->id) }}" target="_blank">{{ $file->name }}</a>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
        @if(count($folder->children_tree))
            <ul class="folder-list">
                @foreach($folder->children_tree as $sub)
                    @include('folders._folder_recursive', ['folder' => $sub, 'class' => 'tree-branch'])
                @endforeach
            </ul>
        @endif
    </div>
</li>