(function(blocks, element, components, i18n) {
    var el = element.createElement;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var __ = i18n.__;

    blocks.registerBlockType('link-gallery/pdf-insert', {
        title: 'PDF リンク挿入',
        icon: 'pdf',
        category: 'common',
        attributes: {
            pdfId: {
                type: 'number'
            },
            buttonText: {
                type: 'string',
                default: ''
            },
            pdfUrl: {
                type: 'string',
                default: ''
            },
            openInNewTab: {
                type: 'boolean',
                default: true
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            // PDF 选项列表
            var pdfOptions = [
                { label: 'PDFを選択', value: '' }
            ];

            if (window.pdfGalleryData && window.pdfGalleryData.pdfs) {
                pdfGalleryData.pdfs.forEach(function(pdf) {
                    // 限制文件名长度为30个字符
                    var displayName = pdf.filename;
                    if (displayName.length > 30) {
                        displayName = displayName.substring(0, 27) + '...';
                    }
                    pdfOptions.push({
                        label: displayName,
                        value: pdf.id
                    });
                });
            }

            // 当选择PDF文件时，自动设置按钮文字
          function onPdfSelect(value) {
                let pdfGalleryData = window.pdfGalleryData;
                var pdfId = parseInt(value, 10);
                var buttonText = '';
                setAttributes({ pdfId: pdfId });

                if (pdfId) {
                    var selectedPdf = pdfGalleryData.pdfs.find(function(pdf) {
                        console.log('Comparing:', pdf.id, pdfId, typeof pdf.id, typeof pdfId);
                        return pdf.id === pdfId;
                    });

                    if (selectedPdf) {
                        // 移除.pdf扩展名
                        buttonText = selectedPdf.filename;
                        // var fileNameWithoutExt = buttonText.replace(/\.pdf$/i, '');
                        var fileNameWithoutExt = buttonText;

                        // 如果文件名超过30个字符，则截断
                        if (fileNameWithoutExt.length > 30) {
                            fileNameWithoutExt = fileNameWithoutExt.substring(0, 27) + '...';
                        }

                        // 设置按钮文字和URL
                        setAttributes({
                            buttonText: fileNameWithoutExt,
                            pdfUrl: selectedPdf.url,
                            pdfTitle: fileNameWithoutExt
                        });
                    }
                } else {
                    // 当没有选择PDF时，清空相关属性
                  setAttributes({
                        pdfId: 0,
                        buttonText: '',
                        pdfUrl: '',
                        pdfTitle: ''
                    });
                }
            }

            return el('div', { className: 'protected-pdf-block-editor' },
                el('div', {
                    style: {
                        padding: '20px',
                        backgroundColor: '#f5f5f5',
                        border: '1px solid #ddd'
                    }
                },
                    el(SelectControl, {
                        label: 'PDF ファイル',
                        value: attributes.pdfId || '',
                        options: pdfOptions,
                        onChange: onPdfSelect
                    }),
                    el(TextControl, {
                        label: 'ボタンテキスト',
                        value: attributes.buttonText || '',
                        onChange: function(value) {
                            setAttributes({ buttonText: value, pdfTitle: value });
                        }
                    }),
                    !attributes.pdfId && el('p', {
                        style: {
                            color: '#666'
                        }
                    }, '※ PDFファイルを選択してください。新しいPDFは「PDF ダウンロード管理」で追加できます。')
                )
            );
        },

        save: function() {
            return null;
        }
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.i18n
));
