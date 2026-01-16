<template>
  <div class="rich-text-editor">
    <div class="toolbar">
      <el-button-group>
        <el-button size="small" @click="execCommand('bold')" title="粗体">
          <strong>B</strong>
        </el-button>
        <el-button size="small" @click="execCommand('italic')" title="斜体">
          <em>I</em>
        </el-button>
        <el-button size="small" @click="execCommand('underline')" title="下划线">
          <u>U</u>
        </el-button>
      </el-button-group>
      
      <el-button-group style="margin-left: 10px">
        <el-button size="small" @click="execCommand('justifyLeft')" title="左对齐">
          ≡
        </el-button>
        <el-button size="small" @click="execCommand('justifyCenter')" title="居中">
          ≡
        </el-button>
        <el-button size="small" @click="execCommand('justifyRight')" title="右对齐">
          ≡
        </el-button>
      </el-button-group>
      
      <el-button-group style="margin-left: 10px">
        <el-button size="small" @click="execCommand('insertUnorderedList')" title="无序列表">
          ⋮
        </el-button>
        <el-button size="small" @click="execCommand('insertOrderedList')" title="有序列表">
          1.
        </el-button>
      </el-button-group>
      
      <el-select
        v-model="fontSize"
        size="small"
        @change="changeFontSize"
        placeholder="字号"
        style="width: 100px; margin-left: 10px"
      >
        <el-option label="12px" value="1" />
        <el-option label="14px" value="2" />
        <el-option label="16px" value="3" />
        <el-option label="18px" value="4" />
        <el-option label="24px" value="5" />
        <el-option label="32px" value="6" />
        <el-option label="48px" value="7" />
      </el-select>
      
      <el-color-picker
        v-model="textColor"
        @change="changeColor"
        size="small"
        style="margin-left: 10px"
      />
      
      <el-button size="small" @click="insertLink" style="margin-left: 10px" title="插入链接">
        🔗
      </el-button>
      
      <el-button size="small" @click="viewSource" style="margin-left: 10px" title="查看源码">
        &lt;/&gt;
      </el-button>
    </div>
    
    <div
      ref="editorRef"
      class="editor-content"
      contenteditable="true"
      @input="handleInput"
      @paste="handlePaste"
      v-html="modelValue"
    ></div>
    
    <!-- 源码编辑对话框 -->
    <el-dialog v-model="sourceVisible" title="HTML 源码" width="800px">
      <el-input
        v-model="sourceCode"
        type="textarea"
        :rows="20"
        placeholder="请输入 HTML 代码"
      />
      <template #footer>
        <el-button @click="sourceVisible = false">取消</el-button>
        <el-button type="primary" @click="applySource">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ElMessageBox } from 'element-plus'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref()
const fontSize = ref('3')
const textColor = ref('#000000')
const sourceVisible = ref(false)
const sourceCode = ref('')

// 执行编辑命令
const execCommand = (command, value = null) => {
  document.execCommand(command, false, value)
  editorRef.value.focus()
}

// 改变字号
const changeFontSize = (value) => {
  execCommand('fontSize', value)
}

// 改变颜色
const changeColor = (color) => {
  execCommand('foreColor', color)
}

// 插入链接
const insertLink = async () => {
  try {
    const { value: url } = await ElMessageBox.prompt('请输入链接地址', '插入链接', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      inputPattern: /^https?:\/\/.+/,
      inputErrorMessage: '请输入有效的URL'
    })
    
    if (url) {
      execCommand('createLink', url)
    }
  } catch {
    // 用户取消
  }
}

// 查看源码
const viewSource = () => {
  sourceCode.value = editorRef.value.innerHTML
  sourceVisible.value = true
}

// 应用源码
const applySource = () => {
  editorRef.value.innerHTML = sourceCode.value
  emit('update:modelValue', sourceCode.value)
  sourceVisible.value = false
}

// 处理输入
const handleInput = () => {
  const content = editorRef.value.innerHTML
  emit('update:modelValue', content)
}

// 处理粘贴（清理格式）
const handlePaste = (e) => {
  e.preventDefault()
  const text = e.clipboardData.getData('text/plain')
  document.execCommand('insertText', false, text)
}

// 监听外部变化
watch(() => props.modelValue, (newValue) => {
  if (editorRef.value && editorRef.value.innerHTML !== newValue) {
    editorRef.value.innerHTML = newValue
  }
})
</script>

<style scoped lang="scss">
.rich-text-editor {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  
  .toolbar {
    padding: 10px;
    background: #f5f7fa;
    border-bottom: 1px solid #dcdfe6;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
  }
  
  .editor-content {
    min-height: 300px;
    max-height: 500px;
    overflow-y: auto;
    padding: 15px;
    background: white;
    outline: none;
    
    &:focus {
      outline: none;
    }
    
    // 编辑器内容样式
    :deep(p) {
      margin: 0 0 10px 0;
    }
    
    :deep(ul), :deep(ol) {
      margin: 10px 0;
      padding-left: 30px;
    }
    
    :deep(a) {
      color: #409eff;
      text-decoration: underline;
    }
    
    :deep(img) {
      max-width: 100%;
      height: auto;
    }
  }
}
</style>
