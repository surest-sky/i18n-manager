<?php 
$method = $_SERVER['REQUEST_METHOD'];
if(strtoupper($method) == 'POST') {
    $data = file_get_contents("php://input");
    if(!$data) {
        return false;
    }

    $data = json_decode($data, true);
    foreach($data as $key => $item) {
        $filename = $key . '.json';
        file_put_contents($filename, json_encode($item));
    }
    echo "修改成功";
}


$filenames = scandir('./');
$keys = array_filter($filenames, function($filename) {
    return strpos($filename, '.json') > -1;
});
$keys = array_map(function($key) {
    return str_replace('.json', '', $key);
}, $keys);
$keys = json_encode(array_values($keys))

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <!-- import CSS -->
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script src="https://unpkg.com/dayjs@1.8.21/dayjs.min.js"></script>
    <script src="//cdn.quilljs.com/1.3.6/quill.js"></script>
    <link rel="stylesheet" href="//cdn.quilljs.com/1.3.6/quill.bubble.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-y: hidden;
        }

        #app {
            width: 1100px;
            margin: 0 auto;
            padding: 0px 8px 0 8px;
            border: 1px solid #f3f3f3;
        }

        .menu {
            height: calc(100vh - 100px);
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        .menu .item {
            padding-left: 10px;
            width: 100%;
            height: 30px;
            line-height: 30px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .menu .item:hover {
            background-color: #efefef;
        }

        .menu .item.active {
            background-color: #efefef;
        }

        .menu .item .el-icon-delete {
            display: none;
        }

        .menu .item.active .el-icon-delete {
            display: inline-block;
        }

        .menu .danger {
            color: red;
        }

        .menu .el-icon-delete {
            cursor: pointer;
            color: rgb(255, 133, 133)
        }

        .menu .el-icon-delete:hover {
            color: red
        }

        .menu {
            position: relative;
        }

        .el-backtop {
            position: fixed;
            right: 40px;
            bottom: 100px;
        }

        .el-backbottom {
            position: fixed;
            right: 40px;
            bottom: 40px;
        }

        .el-backbottom .el-icon-caret-top {
            transform: rotate(180deg);
        }

        .scrollbar::-webkit-scrollbar-track {
            background-color: #f5f5f5;
        }

        .scrollbar::-webkit-scrollbar {
            width: 5px;
            background-color: #f5f5f5;
        }

        .scrollbar::-webkit-scrollbar-thumb {
            background-color: #f90;
            background-image: -webkit-linear-gradient(45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
        }

        .translate {
            padding: 0px 10px 10px 10px;
        }

        .translate label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .translate .box-card {
            margin-bottom: 20px;
        }

        .translate .warnging textarea {
            border: 2px solid #f44336 !important;
        }

        .bar-menu {
            display: flex;
            line-height: 45px;
            height: 45px;
            padding-left: 5px;
            border-radius: 5px;
            color: white;
            background-color: #409eff;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            margin-bottom: 30px;
        }

        .bar-menu .search {
            margin-left: 20px;
            border: none;
        }

        .bar-menu .search-input input {
            width: 400px;
        }

        .tip {
            cursor: pointer;
        }

        .tip:hover {
            opacity: 0.8;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .random-node {
            font-size: 12px;
            color: #409eff;
            cursor: pointer;
        }

        .random-node:hover {
            color: #1c85ee;
        }

        .hidden {
            display: none;
        }

        .show {
            display: block;
        }

        .el-card {
            overflow: inherit;
        }

        .el-card__body {
            width: 90%;
            height: 220px;

            z-index: 1;
        }

        .editor {
            border: 1px solid #000000;
            z-index: 999;
            height: 200px
        }
    </style>
</head>

<body>
    <div id="app">
        <el-row :gutter="20" v-loading="loading">

            <div class="bar-menu">
                <div class="title">
                    <span>I18n Manager | </span>
                    <el-tag effect="dark" type="primary" class="tip" @click="filter()">All
                    </el-tag>
                    <el-tag effect="dark" type="danger" class="tip" @click="filter('warning')">
                        {{warngingTotal}}</el-tag>
                    <el-tag effect="dark" type="success" class="tip" @click="filter('success')">{{successTotal}}
                    </el-tag>
                </div>


                <div class="search">
                    <el-select clearable size="mini" @change="filterKey" v-model="selectKey" filterable
                        placeholder="请选择">
                        <el-option v-for="key in filterKeys" :key="key" :label="key" :value="key">
                        </el-option>
                    </el-select>
                </div>

                <div class="search-input search">
                    <el-input v-model="keyword" @change="filterKeyword" placeholder="搜索..." size="mini"></el-input>
                </div>

                <div class="search-input search">
                    <el-button size="mini" @click="showAdd">添加</el-button>
                </div>

                <div class="search-input search">
                    <el-button size="mini" @click="showDelopyVisible = true">部署</el-button>
                </div>
            </div>
            <el-col :span="6">
                <div class="menu scrollbar">
                    <div :class="`item ${translates[key]['type'] == 'warning' ? 'danger' : ''} ${key == translate.key ? 'active' : ''}`"
                        v-for="key in filterKeys" :key="key" @click="select(key)">
                        <span>- {{ key }}</span>
                        <span>
                            <el-badge :value="translates[key]['total']"
                                :type="translates[key]['type'] == 'warning' ? 'danger' : 'primary'">
                            </el-badge>

                            <i class="el-icon-delete" @click="del(key)"></i>
                        </span>
                    </div>


                    <el-tooltip effect="dark" content="回到顶部" placement="left">
                        <div class="el-backtop" @click="scroll('top')"><i class="el-icon-caret-top"></i></div>
                    </el-tooltip>

                    <el-tooltip effect="dark" content="回到底部" placement="left">
                        <div class="el-backtop el-backbottom" @click="scroll()"><i class="el-icon-caret-top"></i>
                        </div>
                    </el-tooltip>
                </div>

            </el-col>
            <el-col :span="12">
                <label for="">{{ translate.key }}</label>
                <div class="translate">
                    <el-card class="box-card " v-for="key in allows" :key="key">
                        <div :class="`${translate[key] ? '' : 'warnging'}`">
                            <label for="">{{ key }}</label>
                            <div class="editor" :id="key"></div>
                            <!-- <el-input type="textarea" id="editor" rows="5" cols="5" v-model="translate[key]" placeholder="请输入内容">
                            </el-input> -->
                        </div>
                    </el-card>

                    <el-button class="submit-button" type="primary" @click="save">提交</el-button>
                </div>
            </el-col>
        </el-row>

        <el-dialog title="创建节点" :visible.sync="addModalVisible" width="500px">
            <div>
                <label for="">节点ID <span class="random-node" @click="setRandomNode">自动创建</span></label>
                <el-input v-model="addNode" placeholder="新节点ID"></el-input>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="addModalVisible = false">取 消</el-button>
                <el-button type="primary" @click="createNode">确 定</el-button>
            </span>
        </el-dialog>

        <el-dialog title="部署" :visible.sync="showDelopyVisible" width="500px">
            <div>
                <el-checkbox-group v-model="checkList">
                    <el-checkbox label="staging">Staging</el-checkbox>
                    <el-checkbox label="master">Master</el-checkbox>
                </el-checkbox-group>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="showDelopyVisible = false">取 消</el-button>
                <el-button type="primary" @click="startDelopy">确 定</el-button>
            </span>
        </el-dialog>
    </div>
</body>
<script>
    new Vue({
        el: '#app',
        data: function () {
            return {
                input: "",
                i18nKeys: [],
                filterKeys: [],
                translates: {},
                loading: true,
                translate: {
                    key: ""
                },
                selectKey: "",
                keyword: "",
                addModalVisible: false,
                addNode: '',
                showDelopyVisible: false,
                checkList: [],
                editors: {}
            }
        },
        computed: {
            warngingTotal() {
                const { translates } = this
                return this.getTypeTotal('warning', translates)
            },
            successTotal() {
                const { translates } = this
                return this.getTypeTotal('success', translates)
            },
            allToal() {
                return this.i18nKeys.length
            },
            allows() {
                const temp = `<?php echo $keys; ?>`
                console.log(JSON.parse(temp))
                return JSON.parse(temp)
            }
        },
        methods: {
            loadEditor() {
                const editorsForDom = document.querySelectorAll(".editor")
                const editors = {}
                editorsForDom.forEach(function (editor) {
                    var quill = new Quill(editor, {
                        theme: 'bubble'
                    });
                    editors[editor.getAttribute('id')] = quill;
                })
                this.editors = editors
            },
            lodaI18n() {
                const keys = []
                this.loading = true
                let i18nItems = {}
                const time = Number(new Date())
                const promises = this.allows.map(async element => {
                    return new Promise((resolve, reject) => {
                        fetch(`${element}.json?version=${time}`).then(async function (response) {
                            const items = await response.json()
                            const _keys = Object.keys(items)
                            keys.push(..._keys)
                            i18nItems[element] = items
                            resolve(true)
                        })
                    })
                });
                const translates = {}
                Promise.all(promises).then(() => {
                    const i18nKeys = Array.from(new Set(keys));
                    i18nKeys.forEach(key => {
                        const translate = {}
                        let total = 0
                        this.allows.forEach(translateKey => {
                            const value = i18nItems[translateKey][key]
                            translate[translateKey] = value
                            if (value) {
                                total++
                            }
                        });

                        translate.total = total
                        translate.type = total < this.allows.length ? 'warning' : 'success'
                        translates[key] = translate
                    })
                    this.i18nKeys = i18nKeys.sort()
                    this.translates = translates
                    this.filterKeys = this.i18nKeys
                    this.loading = false
                    this.select(this.i18nKeys[0])
                })
                this.loadEditor()
            },

            select(i18nKey) {
                this.translate = this.translates[i18nKey]
                this.translate.key = i18nKey

                this.allows.map(key => {
                    this.setContents(`#${key}`, this.translate[key])
                })
            },

            filter(type) {
                this.$notify({
                    message: '过滤完成',
                    type: 'success'
                });

                if (!type) {
                    this.filterKeys = this.i18nKeys
                    return
                }

                const keys = []
                for (var key in this.translates) {
                    const translate = this.translates[key]
                    if (translate.type == type) {
                        keys.push(key)
                    }
                }

                this.filterKeys = keys
            },

            filterKey(value) {
                if (value) {
                    this.filterKeys = [value]
                } else {
                    this.filterKeys = this.i18nKeys
                }
            },

            filterKeyword(keyword) {
                if (!keyword) {
                    this.filterKeys = this.i18nKeys
                    return
                }
                const keys = []
                const { translate, allows } = this
                for (var key in this.translates) {
                    const translate = this.translates[key]
                    allows.forEach(translateKey => {
                        const value = translate[translateKey]
                        if (!value) {
                            return;
                        }
                        if (value.indexOf(keyword) > 0) {
                            keys.push(key)
                        }
                    });
                }

                this.filterKeys = keys
                this.$notify({
                    message: '过滤完成',
                    type: 'success'
                });
            },

            getTypeTotal(type, translates) {
                let total = 0
                for (var key in translates) {
                    const translate = translates[key]
                    if (translate.type == type) {
                        total++
                    }
                }
                return total
            },

            ltrim(str){  //删除左边的空格
                return str.replace(/(^<p>*)/g,"");
            },

            rtrim(str){  //删除右边的空格
                return str.replace(/(<\/p>*$)/g,"");
            },

            getContents(id) {
                let html = document.querySelector(id).children[0].innerHTML
                html = this.ltrim(html)
                html = this.rtrim(html)
                return html
            },

            setContents(id, content) {
                document.querySelector(id).children[0].innerHTML = content
            },

            save() {
                this.allows.map(key => {
                    const html = this.getContents(`#${key}`)
                    this.translate[key] = html
                })
                
                this.translates[this.translate.key] = this.translate
                this.complete()
            },

            complete() {
                const { allows, translates } = this
                const packages = {}
                this.i18nKeys.map(key => {
                    allows.map(translateKey => {
                        if (!packages.hasOwnProperty(translateKey)) {
                            packages[translateKey] = {}
                        }

                        packages[translateKey][key] = translates[key][translateKey]
                    })
                })

                fetch('', {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(packages)
                }).then(() => {
                    this.$notify({
                        message: '更新成功',
                        type: 'success'
                    });
                })
            },

            showAdd() {

                this.addModalVisible = true
            },

            setRandomNode() {
                const no = dayjs().format("YYMMDDmm")
                this.addNode = `A${no}`
            },

            createNode() {
                const { addNode, allows } = this
                if (this.i18nKeys.includes(addNode)) {
                    this.$notify({
                        message: '请不要重复添加',
                        type: 'error'
                    });
                    return
                }

                this.i18nKeys.push(addNode)
                this.filterKeys = this.i18nKeys
                const newTranslate = {}
                allows.forEach(key => {
                    newTranslate[key] = ""
                })
                this.translates[addNode] = newTranslate
                this.addModalVisible = false
                this.select(addNode)

                this.$notify({
                    message: '创建成功',
                    type: 'success'
                });

                setTimeout(() => {
                    this.scroll()
                }, 500)
            },

            scroll(type) {
                const wrapper = document.querySelector(".menu")
                const h = document.querySelector(".menu").scrollHeight
                if (type == 'top') {
                    wrapper.scrollTop = 0
                } else {
                    wrapper.scrollTop = h
                }
            },

            del(key) {
                if (!this.i18nKeys.includes(key)) {
                    this.$notify({
                        message: '删除异常',
                        type: 'error'
                    });
                    return
                }

                this.$confirm('如果不是新增的key, 请联系开发是否能删除 ', '请确定是否继续', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.i18nKeys = this.i18nKeys.filter(index => index != key)
                    this.filterKeys = this.i18nKeys
                    delete this.translates[key]
                    this.$notify({
                        message: '删除成功',
                        type: 'success'
                    });
                })
            },

            startDelopy() {
                console.log('startDelopy', this.checkList)

                const maps = {
                    'staging': 'https://teacherrecord.coding.net/api/cd/webhooks/webhook/2698b5f8-968e-4f81-92e8-b67197c64323',
                    'master': ''
                }

                if (this.checkList.length == 0) {
                    this.$notify({
                        message: '请选择部署环境',
                        type: 'warning'
                    });
                    return
                }

                this.checkList.map(item => {
                    const value = maps[item]
                    if (value) {
                        fetch(value, {
                            method: 'post'
                        }).then(() => {
                            this.$notify({
                                message: `部署进行中:${item}`,
                                type: 'success'
                            });
                        })
                    }
                })
                this.showDelopyVisible = false;
            }

        },
        mounted() {
            this.lodaI18n()
        }
    })
</script>

</html>
