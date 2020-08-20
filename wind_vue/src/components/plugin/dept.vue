<template>
    <div class="dept">
        <el-tree
                :props="nodeProp"
                :load="getCategory"
                lazy
                accordion
                :highlight-current='true'
                :expand-on-click-node="false"
                @node-click="getist"
                :render-content="renderContent"
                :empty-text='emptytext'
                v-if="is_show">
        </el-tree>
    </div>
</template>
<script>
    export default {
        data(){
            return {
                //无数据时候显示的文本
                emptytext:'',
                //分类节点数据
                nodeProp: {
                    label: 'name',
                    isLeaf: 'is_leaf',
                },
            }
        },
        props:{
            httpUrl:String,
            is_list:Boolean,
            is_show:Boolean,
        },
        methods:{
            getCategory(node, resolve){
                //获取分类节点
                let _that = this;
                let url = _that.httpUrl;
                let parentId = node.level === 0 ? 0 : node.data.id;
                let data = {parentId: parentId};
                _that.$post(url, data).then(function (res) {
                    const catdata = res.data;
                    const resData = [];
                    //循环
                    catdata.orgDepartment.forEach(function (item, index) {
                        item.originName= item.name;
                        if(item.name.length>8){
                            item.name = item.name.substr(0,8) + "...";
                        }
                        resData.push(item);
                    });
                    return resolve(resData);
                });
            },
            getist(node) {
                this.$emit('loadList', node)
            },
            //自定义节点样式
            renderContent(h, {node, data, store}) {
                let createElement = arguments[0];
                return createElement('span', {attrs: {class: 'div-span',title:node.data.originName}}, [
                    createElement('i', {attrs: {class: 'tree-i'}}),
                    createElement('span', {attrs: {class: 'tool-span'}}),
                    createElement('span', {attrs: {class: 'text-span'}}, arguments[1].node.label)
                ]);
            },
        },
    }
</script>
<style scoped>
    /*.tree{*/
    /*padding-top: 20px;*/
    /*}*/
</style>