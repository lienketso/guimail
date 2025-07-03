<li>
    <div class="folder-title">
        <i class="fa fa-folder folder-icon"></i> {{ $folder->name }} 
        @if($folder->ngay_nop)
            <span class="ngay-nop">Ngày nộp: {{ $folder->ngay_nop ? date('d/m/Y', strtotime($folder->ngay_nop)) : '' }}</span>
        @endif

        @if($folder->name && Str::contains(Str::lower($folder->name), 'lần'))
             <button type="button" class="btn btn-sm btn-link add-ngay-nop" 
                data-folder-id="{{ $folder->id }}"><i class="fa fa-calendar-plus"></i></button>
              @endif
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
                @include('folders._folder_recursive', ['folder' => $sub])
            @endforeach
        </ul>
    @endif
</li>