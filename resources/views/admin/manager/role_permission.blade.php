@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>权限管理</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">


            <div class="_frame">
                <label class="layui-form-label">权限</label>
                <div class="layui-input-block">
                    <div id="xtree1" class="xtree_contianer  layui-form">
                    </div>
                </div>
                <br>


            </div>
            <input type="hidden" value="{{$admin_role->id}}" name="id">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="permission_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">
        var authorityList="{{$authorityList}}";
        var json = [
            {
                @if(strpos($authorityList,"1000")>0) checked: true, @endif
                title: "交易所业务管理系统",
                value: "1000",
                data: [
                    {
                        @if(strpos($authorityList,"1100")>0) checked: true, @endif
                        title: "平台设置",
                        value: "1100",
                        data: [
                            {
                                title: "基础设置",
                                value: "9990",
                                data: [
                                    {
                                        @if(strpos($authorityList,"9990")>0) checked: true, @endif
                                        title: "查看基础设置",
                                        value: "9990",
                                        data: []
                                    },
                                    {
                                    @if(strpos($authorityList,"9992")>0) checked: true, @endif
                                    title: "基础设置编辑",
                                    value: "9992",
                                    data: []
                                    }



                                ]
                            },

                            {
                                title: "钱包设置",
                                value: "9999",
                                data: [
                                    {
                                        @if(strpos($authorityList,"9999")>0) checked: true, @endif
                                        title: "查看钱包设置",
                                        value: "9999",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"9998")>0) checked: true, @endif
                                        title: "修改钱包设置",
                                        value: "9998",
                                        data: []
                                    }



                                ]
                            },
                            {
                                title: "欧美电汇设置",
                                value: "99990",
                                data: [
                                    {
                                        @if(strpos($authorityList,"999901")>0) checked: true, @endif
                                        title: "查看钱包设置",
                                        value: "999901",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"999802")>0) checked: true, @endif
                                        title: "修改钱包设置",
                                        value: "999802",
                                        data: []
                                    }



                                ]
                            }






                            , {
                                title: "角色管理", value: "1100", data: [
                                    {
                                        @if(strpos($authorityList,"1100")>0) checked: true, @endif
                                        title: "查看角色列表",
                                        value: "1100",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1111")>0) checked: true, @endif
                                        title: "新增角色",
                                        value: "1111",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1112")>0) checked: true, @endif
                                        title: "编辑角色",
                                        value: "1112",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1113")>0) checked: true, @endif
                                        title: "删除角色",
                                        value: "1113",
                                        data: []
                                    }
                                ]
                            }
                            , {
                                title: "后台用户管理", value: "8100", data: [
                                    {
                                        @if(strpos($authorityList,"8100")>0) checked: true, @endif
                                        title: "查看后台用户列表",
                                        value: "8100",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8111")>0) checked: true, @endif
                                        title: "新增后台用户",
                                        value: "8111",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8112")>0) checked: true, @endif
                                        title: "编辑后台用户",
                                        value: "8112",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8113")>0) checked: true, @endif
                                        title: "删除后台用户",
                                        value: "8113",
                                        data: []
                                    }
                                ]
                            },{
                                title: "代理管理", value: "8200", data: [
                                    {
                                        @if(strpos($authorityList,"8200")>0) checked: true, @endif
                                        title: "查看代理列表",
                                        value: "8200",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8201")>0) checked: true, @endif
                                        title: "新增代理",
                                        value: "8201",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8202")>0) checked: true, @endif
                                        title: "编辑代理",
                                        value: "8202",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8203")>0) checked: true, @endif
                                        title: "删除代理",
                                        value: "8203",
                                        data: []
                                    }
                                ]
                            }
                            , {
                                title: "币种管理", value: "1200", data: [
                                    {
                                        @if(strpos($authorityList,"1200")>0) checked: true, @endif
                                        title: "查看币种列表",
                                        value: "1200",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1201")>0) checked: true, @endif
                                        title: "新增币种",
                                        value: "1201",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1202")>0) checked: true, @endif
                                        title: "编辑币种",
                                        value: "1202",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1203")>0) checked: true, @endif
                                        title: "删除角色",
                                        value: "1203",
                                        data: []
                                    }
                                ]
                            }




                            , {
                                @if(strpos($authorityList,"1500")>0) checked: true, @endif
                                title: "资讯管理", value: "1500", data: [
                                    {
                                        @if(strpos($authorityList,"1500")>0) checked: true, @endif
                                        title: "查询资讯列表",
                                        value: "1500",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1504")>0) checked: true, @endif
                                        title: "资讯分类管理",
                                        value: "1504",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1501")>0) checked: true, @endif
                                        title: "发布资讯",
                                        value: "1501",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1502")>0) checked: true, @endif
                                        title: "编辑资讯",
                                        value: "1502",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1503")>0) checked: true, @endif
                                        title: "删除资讯",
                                        value: "1503",
                                        data: []
                                    }
                                ]
                            }
                            , {
                                @if(strpos($authorityList,"1600")>0) checked: true, @endif
                                title: "机器人管理", value: "1600", data: [
                                    {
                                        @if(strpos($authorityList,"1600")>0) checked: true, @endif
                                        title: "查看机器人列表",
                                        value: "1600",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1601")>0) checked: true, @endif
                                        title: "添加机器人",
                                        value: "1601",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1602")>0) checked: true, @endif
                                        title: "编辑机器人",
                                        value: "1602",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1603")>0) checked: true, @endif
                                        title: "删除机器人",
                                        value: "1603",
                                        data: []
                                    }
                                ]
                            }





                        ]
                    }
                    ,
                    {

                        title: "交易管理", value: "2000", data: [
                            {
                                @if(strpos($authorityList,"2001")>0) checked: true, @endif
                                title: "查看日志信息列表",
                                value: "2001",
                                data: [


                                ]

                            },
                            {
                                @if(strpos($authorityList,"2100")>0) checked: true, @endif
                                title: "撮合交易",
                                value: "2100",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2100")>0) checked: true, @endif
                                        title: "查看撮合交易列表",
                                        value: "2100",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2101")>0) checked: true, @endif
                                        title: "撤回",
                                        value: "2101",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2102")>0) checked: true, @endif
                                        title: "删除",
                                        value: "2102",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2103")>0) checked: true, @endif
                                        title: "强制交易",
                                        value: "2103",
                                        data: []

                                    },



                                ]

                            },
                            {
                                @if(strpos($authorityList,"2200")>0) checked: true, @endif
                                title: "法币交易",
                                value: "2200",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2201")>0) checked: true, @endif
                                        title: "A",
                                        value: "2201",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2202")>0) checked: true, @endif
                                        title: "B",
                                        value: "2202",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2203")>0) checked: true, @endif
                                        title: "C",
                                        value: "2203",
                                        data: []

                                    },




                                ]

                            },
                            {
                                @if(strpos($authorityList,"2300")>0) checked: true, @endif
                                title: "杠杆交易",
                                value: "2300",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2301")>0) checked: true, @endif
                                        title: "平仓",
                                        value: "2301",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2302")>0) checked: true, @endif
                                        title: "搜索按钮",
                                        value: "2302",
                                        data: []

                                    },





                                ]

                            },


                            {
                                @if(strpos($authorityList,"2400")>0) checked: true, @endif
                                title: "用户风险率",
                                value: "2400",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2400")>0) checked: true, @endif
                                        title: "查询用户风险率列表",
                                        value: "2400",
                                        data: []

                                    },






                                ]

                            }
                            ,
                            {
                                @if(strpos($authorityList,"2500")>0) checked: true, @endif
                                title: "资金流水",
                                value: "2500",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2500")>0) checked: true, @endif
                                        title: "查询资金流水列表",
                                        value: "2500",
                                        data: []

                                    },






                                ]

                            },
                            {
                                @if(strpos($authorityList,"2600")>0) checked: true, @endif
                                title: "秒合约交易",
                                value: "2600",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2600")>0) checked: true, @endif
                                        title: "查看秒合约交易列表",
                                        value: "2600",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2601")>0) checked: true, @endif
                                        title: "编辑",
                                        value: "2601",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2602")>0) checked: true, @endif
                                        title: "Spread",
                                        value: "2602",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2603")>0) checked: true, @endif
                                        title: "预设",
                                        value: "2603",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2604")>0) checked: true, @endif
                                        title: "搜索",
                                        value: "2604",
                                        data: []

                                    }






                                ]

                            },


                            {
                                @if(strpos($authorityList,"2800")>0) checked: true, @endif
                                title: "秒合约设置",
                                value: "2800",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2800")>0) checked: true, @endif
                                        title: "查看秒合约设置",
                                        value: "2800",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2801")>0) checked: true, @endif
                                        title: "编辑",
                                        value: "2801",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2702")>0) checked: true, @endif
                                        title: "修改状态",
                                        value: "2702",
                                        data: []

                                    },

                                ]

                            },

                            {
                                @if(strpos($authorityList,"2900")>0) checked: true, @endif
                                title: "提币申请",
                                value: "2900",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2900")>0) checked: true, @endif
                                        title: "查看提币申请列表",
                                        value: "2900",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2902")>0) checked: true, @endif
                                        title: "提币审核",
                                        value: "2902",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2903")>0) checked: true, @endif
                                        title: "导出提币记录",
                                        value: "2903",
                                        data: []

                                    }

                                ]

                            },

                            {
                                @if(strpos($authorityList,"2950")>0) checked: true, @endif
                                title: "充币申请",
                                value: "2950",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2950")>0) checked: true, @endif
                                        title: "查看充币申请列表",
                                        value: "2950",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2952")>0) checked: true, @endif
                                        title: "充币审核",
                                        value: "2952",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"2953")>0) checked: true, @endif
                                        title: "导出充币记录",
                                        value: "2953",
                                        data: []

                                    }

                                ]

                            },
                            {
                                @if(strpos($authorityList,"1400")>0) checked: true, @endif
                                title: "邀请返佣", value: "1400", data: [
                                    {
                                        @if(strpos($authorityList,"1400")>0) checked: true, @endif
                                        title: "查看邀请返佣列表",
                                        value: "1400",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1402")>0) checked: true, @endif
                                        title: "变更上级",
                                        value: "1402",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"1403")>0) checked: true, @endif
                                        title: "其他",
                                        value: "1403",
                                        data: []
                                    }
                                ]
                            },
                            {
                                @if(strpos($authorityList,"8477")>0) checked: true, @endif
                                title: "充值组团返佣", value: "8477", data: [
                                    {
                                        @if(strpos($authorityList,"8477")>0) checked: true, @endif
                                        title: "查看充值组团返佣列表",
                                        value: "8477",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8478")>0) checked: true, @endif
                                        title: "审核充值组团返佣",
                                        value: "8478",
                                        data: []
                                    },

                                ]
                            },
                            {
                                @if(strpos($authorityList,"8479")>0) checked: true, @endif
                                title: "挖矿组团返佣", value: "8479", data: [
                                    {
                                        @if(strpos($authorityList,"8479")>0) checked: true, @endif
                                        title: "查看挖矿组团返佣列表",
                                        value: "8479",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"8480")>0) checked: true, @endif
                                        title: "审核挖矿组团返佣",
                                        value: "8480",
                                        data: []
                                    },

                                ]
                            },


                        ]
                    },
                    {
                        @if(strpos($authorityList,"3000")>0) checked: true, @endif
                        title: "理财管理", value: "3000", data: [
                            {
                                @if(strpos($authorityList,"3001")>0) checked: true, @endif
                                title: "锁仓挖矿产品列表",
                                value: "3001",
                                data: [
                                    {
                                        @if(strpos($authorityList,"3001")>0) checked: true, @endif
                                        title: "查看锁仓挖矿产品",
                                        value: "3001",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3005")>0) checked: true, @endif
                                        title: "新增锁仓挖矿产品",
                                        value: "3001",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3003")>0) checked: true, @endif
                                        title: "编辑",
                                        value: "3003",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3004")>0) checked: true, @endif
                                        title: "删除",
                                        value: "3004",
                                        data: []

                                    }
                                ]

                            },
                            {
                                @if(strpos($authorityList,"3100")>0) checked: true, @endif
                                title: "锁仓挖矿订单",
                                value: "3100",
                                data: [
                                    {
                                        @if(strpos($authorityList,"3100")>0) checked: true, @endif
                                        title: "查看锁仓挖矿订单列表",
                                        value: "3100",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3101")>0) checked: true, @endif
                                        title: "编辑锁仓挖矿订单",
                                        value: "3101",
                                        data: []

                                    },

                                ]

                            },
                            {
                                @if(strpos($authorityList,"3200")>0) checked: true, @endif
                                title: "申购产品",
                                value: "3200",
                                data: [
                                    {
                                        @if(strpos($authorityList,"3200")>0) checked: true, @endif
                                        title: "查看申购产品列表",
                                        value: "3200",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3201")>0) checked: true, @endif
                                        title: "新增申购产品",
                                        value: "3201",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3202")>0) checked: true, @endif
                                        title: "编辑申购产品",
                                        value: "3202",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3203")>0) checked: true, @endif
                                        title: "删除申购产品",
                                        value: "3203",
                                        data: []

                                    },
                                ]

                            }
                            ,{
                                @if(strpos($authorityList,"3300")>0) checked: true, @endif
                                title: "申购订单",
                                value: "3300",
                                data: [
                                    {
                                        @if(strpos($authorityList,"3300")>0) checked: true, @endif
                                        title: "查询申购订单列表",
                                        value: "3300",
                                        data: []

                                    },
                                    {
                                        @if(strpos($authorityList,"3302")>0) checked: true, @endif
                                        title: "审核",
                                        value: "3302",
                                        data: []

                                    },

                                ]

                            }
                        ]
                    },
                    {

                        title: "客户管理",
                        value: "9000", data: [
                            {
                                title: "客户列表", value: "9101", data: [
                                    {
                                        @if(strpos($authorityList,"9101")>0) checked: true, @endif
                                        title: "查看客户列表",
                                        value: "9101",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"9103")>0) checked: true, @endif
                                        title: "编辑",
                                        value: "9103",
                                        data: [
                                             {
                                                @if(strpos($authorityList,"91031")>0) checked: true, @endif
                                                title: "邮箱编辑",
                                                value: "91031",
                                                data: []
                                            },
                                        ]
                                    },
                                    {
                                        @if(strpos($authorityList,"9104")>0) checked: true, @endif
                                        title: "钱包管理",
                                        value: "9104",
                                        data: [
                                            {
                                                @if(strpos($authorityList,"91041")>0) checked: true, @endif
                                                title: "查看钱包管理",
                                                value: "91041",
                                                data: []
                                            },
                                            {
                                                @if(strpos($authorityList,"91042")>0) checked: true, @endif
                                                title: "调节账户",
                                                value: "91042",
                                                data: []
                                            },
                                        ]
                                    },
                                ]
                            },
                            {
                                title: "实名认证管理", value: "9001", data: [
                                    {
                                        @if(strpos($authorityList,"9001")>0) checked: true, @endif
                                        title: "查看实名认证管理",
                                        value: "9001",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"9003")>0) checked: true, @endif
                                        title: "审核",
                                        value: "9003",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"9004")>0) checked: true, @endif
                                        title: "删除",
                                        value: "9004",
                                        data: []
                                    },
                                ]
                            }
                            ,
                            {
                                @if(strpos($authorityList,"2700")>0) checked: true, @endif
                                title: "投诉建议",
                                value: "2700",
                                data: [
                                    {
                                        @if(strpos($authorityList,"2700")>0) checked: true, @endif
                                        title: "查看投诉建议",
                                        value: "2700",
                                        data: []

                                    },

                                ]

                            }

                        ]
                    },

                    {

                        title: "C2C管理",
                        value: "C9000", data: [
                            {
                                title: "商户管理", value: "C9101", data: [
                                    {
                                        @if(strpos($authorityList,"C9101")>0) checked: true, @endif
                                        title: "查看商户列表",
                                        value: "9101",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"C9103")>0) checked: true, @endif
                                        title: "商户审核",
                                        value: "C9103",
                                        data: []
                                    }
                                ]
                            },
                            {
                                title: "C2C挂单需求", value: "C9001", data: [
                                    {
                                        @if(strpos($authorityList,"C9001")>0) checked: true, @endif
                                        title: "查看实名认证管理",
                                        value: "C9001",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"C9003")>0) checked: true, @endif
                                        title: "审核",
                                        value: "C9003",
                                        data: []
                                    },
                                    {
                                        @if(strpos($authorityList,"C9004")>0) checked: true, @endif
                                        title: "删除",
                                        value: "C9004",
                                        data: []
                                    },
                                ]
                            }
                            ,
                            {
                                @if(strpos($authorityList,"C2700")>0) checked: true, @endif
                                title: "C2C申诉",
                                value: "2700",
                                data: [
                                    {
                                        @if(strpos($authorityList,"C2700")>0) checked: true, @endif
                                        title: "C2C申诉",
                                        value: "C2700",
                                        data: []

                                    },

                                ]

                            }

                        ]
                    }

                ]
            }
        ];


        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;

            var xtree1 = new layuiXtree({
                elem: 'xtree1'   //(必填) 放置xtree的容器，样式参照 .xtree_contianer
                , form: form     //(必填) layui 的 from
                , data: json     //(必填) json数据
                , isopen: true
            });

            form.on('submit(permission_submit)', function (data) {
                var oCks = xtree1.GetChecked(); //这是方法
                var aaaa = '';
                for (var i = 0; i < oCks.length; i++) {
                    aaaa += oCks[i].value + ',';
                }

                var data = data.field;
                data.authortityList=","+aaaa;
                $.ajax({
                    url: '/admin/manager/role_permission_v2',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });


            form.on('checkbox(allCh)',function(data){
                var index = data.value;
                $("input[name='permission[" + index + "][]']").each(function (i,obj) {
                    obj.checked = data.elem.checked;
                });
                form.render('checkbox');
            });

        });
    </script>
@stop