<li>
    <div class="{{ isset($class) ? $class : '' }}">
        <div class="folder-title" style="display: flex; align-items: center; justify-content: space-between;">
            <span>
                <i class="fa fa-folder folder-icon"></i> {{ $folder->name }} 
                @if($folder->ngay_nop)
                    <span class="ngay-nop">Ngày nộp: {{ $folder->ngay_nop ? date('d/m/Y', strtotime($folder->ngay_nop)) : '' }}</span>
                @endif 
            </span>
            @if($folder->name && (Str::contains(Str::lower($folder->name), 'quý') || Str::contains(Str::lower($folder->name), 'q')))
                @php
                    $soLanNop = collect($folder->children_tree)->filter(function($child) {
                        return Str::startsWith(Str::lower($child->name), 'lần');
                    })->count();
                @endphp
                <span class="so-lan-nop">L{{ $soLanNop }}</span>
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
                    @include('folders._folder_recursive_year', ['folder' => $sub, 'class' => 'tree-branch'])
                @endforeach
            </ul>
        @endif
    </div>
</li>