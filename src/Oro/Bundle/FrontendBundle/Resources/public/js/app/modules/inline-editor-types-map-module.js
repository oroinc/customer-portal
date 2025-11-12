import frontendTypeMap from 'oroform/js/tools/frontend-type-map';
import viewerTitle from 'orofrontend/js/app/views/viewer/title-view';
import viewerText from 'orofrontend/js/app/views/viewer/text-view';
import viewerWrapper from 'orofrontend/js/app/views/inline-editable-wrapper-view';

import editorTitle from 'orofrontend/js/app/views/editor/title-editor-view';
import editorText from 'oroform/js/app/views/editor/text-editor-view';
import editorNumber from 'oroform/js/app/views/editor/number-editor-view';
import editorSelect from 'oroform/js/app/views/editor/select-editor-view';
import editorMultilineText from 'orofrontend/js/app/views/editor/multiline-text-editor-view';

frontendTypeMap.title = {viewer: viewerTitle, viewerWrapper, editor: editorTitle};
frontendTypeMap.text = {viewer: viewerText, viewerWrapper, editor: editorText};
frontendTypeMap.number = {viewer: viewerText, viewerWrapper, editor: editorNumber};
frontendTypeMap.select = {viewer: viewerText, viewerWrapper, editor: editorSelect};
frontendTypeMap.multilineText = {viewer: viewerText, viewerWrapper, editor: editorMultilineText};

export default frontendTypeMap;
